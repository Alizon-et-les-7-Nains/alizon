import { Chart } from 'https://cdn.jsdelivr.net/npm/chart.js/auto/+esm';
import { dayChart, weekChart, monthChart, yearChart } from './charts.js';
import moment from 'https://cdn.jsdelivr.net/npm/moment/+esm';

const productsSelector = document.getElementById('product');

async function fetchData(apiQuery) {
    const res = await fetch(apiQuery);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
}

let data = await fetchData('/api/stats');

const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

// Stockage des données par chronologie
const daysData = {};
const weeksData = {};
const monthsData = {};
const yearsData = {};

// Chronologie sélectionnée
let selected = document.querySelector('.selected');

// Gestion des pages
let index = 0;
let maxIndex = 0;

let sortedDaysKeys = [];
let sortedWeeksKeys = [];

function buildData() {
    Object.keys(daysData).forEach(k => delete daysData[k]);
    Object.keys(weeksData).forEach(k => delete weeksData[k]);
    Object.keys(monthsData).forEach(k => delete monthsData[k]);
    Object.keys(yearsData).forEach(k => delete yearsData[k]);

    for (const d of data) {
    // Split by day
    const week = moment(d.dateCommande).format('WW/YYYY');
    const day = moment(d.dateCommande).isoWeekday();

    if (!daysData[week]) {
        daysData[week] = {
            1: { vente: 0, argent: 0 },
            2: { vente: 0, argent: 0 },
            3: { vente: 0, argent: 0 },
            4: { vente: 0, argent: 0 },
            5: { vente: 0, argent: 0 },
            6: { vente: 0, argent: 0 },
            7: { vente: 0, argent: 0 },
        }
    }

    daysData[week][day].vente += parseInt(d.quantite);
    daysData[week][day].argent = Math.round((daysData[week][day].argent + parseFloat(d.prixProduitHt) * parseInt(d.quantite)) * 100) / 100;

    // Split by week
    const month = months[moment(d.dateCommande).month()];
    const N_DAYS = Object.fromEntries([
        ...["Janvier","Mars","Mai","Juillet","Août","Octobre","Décembre"].map(m => [m, 31]),
        ...["Avril","Juin","Septembre","Novembre"].map(m => [m, 30])
    ])
    const nFevrier = moment(d.dateCommande).year() % 4 == 0 ? 29 : 28;
    const nDays = month == 'Février' ? nFevrier : N_DAYS[month];
    const nWeeks = nDays != 28 ? 5 : 4;

    const mont = moment(d.dateCommande).format('MM/YYYY');
    if (!weeksData[mont]) {
        weeksData[mont] = {};
        for (let i = 1; i <= nWeeks; i++) {
            weeksData[mont][i] = {
                vente: 0,
                argent: 0
            }
        }
    }

    const nWeek = Math.ceil(moment(d.dateCommande).date() / 7);
    weeksData[mont][nWeek].vente += parseInt(d.quantite);
    weeksData[mont][nWeek].argent += parseFloat(d.prixProduitHt) * parseInt(d.quantite);
    weeksData[mont][nWeek].argent = Math.round(weeksData[mont][nWeek].argent * 100) / 100;

    // Split by months
    const yea = moment(d.dateCommande).year();
    const mo = months[moment(d.dateCommande).month()];
    if (!monthsData[yea]) {
        monthsData[yea] = {};
        for (let mon = 0; mon < 12; mon++) {
            monthsData[yea][months[mon]] = {
                vente: 0,
                argent: 0
            }
        }
    }

    monthsData[yea][mo].vente += parseInt(d.quantite);
    monthsData[yea][mo].argent += parseFloat(d.prixProduitHt) * parseInt(d.quantite);
    monthsData[yea][mo].argent = Math.round(monthsData[yea][mo].argent * 100) / 100;

    // Split by year
    const year = moment(d.dateCommande).year();
    if (!yearsData[year]) {
        yearsData[year] = {
            vente: 0,
            argent: 0
        }
    }

    yearsData[year].vente += parseInt(d.quantite);
    yearsData[year].argent += parseFloat(d.prixProduitHt) * parseInt(d.quantite);
    yearsData[year].argent = Math.round(yearsData[year].argent * 100) / 100;
    }

    sortedDaysKeys.splice(0, Infinity, ...Object.keys(daysData).sort((a, b) => {
        const [wa, ya] = a.split('/');
        const [wb, yb] = b.split('/');
        return ya !== yb ? ya - yb : wa - wb;
    }))

    sortedWeeksKeys.splice(0, Infinity, ...Object.keys(weeksData).sort((a, b) => {
        const [ma, ya] = a.split('/');
        const [mb, yb] = b.split('/');
        return ya !== yb ? ya - yb : ma - mb;
    }))
}

buildData();

let [vente, argent] = [[], []];
let week = sortedDaysKeys.length - 1;
for (const d in daysData[sortedDaysKeys[week]]) {
    vente.push(daysData[sortedDaysKeys[week]][d].vente);
    argent.push(daysData[sortedDaysKeys[week]][d].argent);
}

document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
let total = argent.reduce((a, b) => a + b, 0);
let formatted = Number.isInteger(total) ? total + '€' : total.toFixed(2) + '€';
document.getElementById('argents').innerHTML = formatted;

const canva = document.getElementById('stats');
let chart = new Chart(canva, dayChart(vente, argent));

document.getElementById('prev').disabled = sortedDaysKeys.length == 1;

function getWeekLabel(weekKey) {
    if (!weekKey) return '';
    const [w, y] = weekKey.split('/');
    return `Semaine du ${moment().year(y).isoWeek(w).startOf('isoWeek').format('DD/MM')} au ${moment().year(y).isoWeek(w).startOf('isoWeek').add(6, 'days').format('DD/MM')}`;
}

function getMonthLabel(month) {
    if (isNaN(month)) return;
    return `${months[moment(sortedWeeksKeys[month], 'MM/YYYY').month()]} ${moment(sortedWeeksKeys[month], 'MM/YYYY').year()}`;
}

document.querySelector('article h3').innerHTML = getWeekLabel(sortedDaysKeys[week]);

function updateStats() {
    chart.destroy();
    [vente, argent] = [[], []];

    switch (selected.innerHTML) {
        case 'Journalier':
            week = sortedDaysKeys.length - 1 - index;

            if (!sortedDaysKeys[week] || !daysData[sortedDaysKeys[week]]) break;

            for (const d in daysData[sortedDaysKeys[week]]) {
                vente.push(daysData[sortedDaysKeys[week]][d].vente);
                argent.push(daysData[sortedDaysKeys[week]][d].argent);
            }

            document.querySelector('article h3').innerHTML = getWeekLabel(sortedDaysKeys[week]);

            chart = new Chart(canva, dayChart(vente, argent));

            maxIndex = sortedDaysKeys.length - 1;

            break;
        
        case 'Hebdomadaire':
            const month = sortedWeeksKeys.length - 1 - index;

            if (!sortedWeeksKeys[month] || !weeksData[sortedWeeksKeys[month]]) break;

            for (const w in weeksData[sortedWeeksKeys[month]]) {
                vente.push(weeksData[sortedWeeksKeys[month]][w].vente);
                argent.push(weeksData[sortedWeeksKeys[month]][w].argent);
            }

            document.querySelector('article h3').innerHTML = getMonthLabel(month);

            chart = new Chart(canva, weekChart(vente, argent, Object.keys(weeksData[sortedWeeksKeys[month]]) ?? ''));

            maxIndex = sortedWeeksKeys.length - 1;

            break;
        
        case 'Mensuel':
            const year = Object.keys(monthsData).length - 1 - index;

            if (!Object.values(monthsData)[year]) break;

            for (const m in Object.values(monthsData)[year]) {
                vente.push(Object.values(monthsData)[year][m].vente);
                argent.push(Object.values(monthsData)[year][m].argent);
            }

            maxIndex = Object.keys(monthsData).length - 1;

            document.querySelector('article h3').innerHTML = Object.keys(monthsData)[year];

            chart = new Chart(canva, monthChart(vente, argent));
    
            break;
        
        case 'Annuel':
            if (!Object.keys(yearsData).length) break;

            for (const y in yearsData) {
                vente.push(yearsData[y].vente);
                argent.push(yearsData[y].argent);
            }

            chart = new Chart(canva, yearChart(vente, argent, Object.keys(yearsData)));

            document.getElementById('prev').disabled = true;
            document.getElementById('next').disabled = true;
            document.querySelector('article h3').innerHTML = '';
            maxIndex = 0;

            break;
    }

    document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
    total = argent.reduce((a, b) => a + b, 0);
    formatted = Number.isInteger(total) ? total + '€' : total.toFixed(2) + '€';
    document.getElementById('argents').innerHTML = formatted;
}

maxIndex = sortedDaysKeys.length - 1;

function updateButtonStates() {
    document.getElementById('next').disabled = index === 0;
    document.getElementById('prev').disabled = index === maxIndex;
}

document.getElementById('prev').addEventListener('click', () => {
    index++;
    updateStats();
    updateButtonStates();
})

document.getElementById('next').addEventListener('click', () => {
    index--;
    updateStats();
    updateButtonStates();
})

document.querySelectorAll('button:not(#prev, #next)').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelector('.selected:not(#prev, #next)').classList.remove('selected');
        btn.classList.add('selected');

        selected = document.querySelector('.selected');

        chart.destroy();
        [vente, argent] = [[], []];

        index = 0;

        switch (selected.innerHTML) {
            case 'Journalier':
                week = sortedDaysKeys.length - 1;

                if (!sortedDaysKeys[week] || !daysData[sortedDaysKeys[week]]) break;

                for (const d in daysData[sortedDaysKeys[week]]) {
                    vente.push(daysData[sortedDaysKeys[week]][d].vente);
                    argent.push(daysData[sortedDaysKeys[week]][d].argent);
                }

                chart = new Chart(canva, dayChart(vente, argent));

                document.querySelector('article h3').innerHTML = getWeekLabel(sortedDaysKeys[week]);

                maxIndex = sortedDaysKeys.length - 1;

                document.getElementById('prev').disabled = index == maxIndex;
                document.getElementById('next').disabled = true;

                break;

            case 'Hebdomadaire':
                const month = sortedWeeksKeys.length - 1;

                if (!sortedWeeksKeys[month] || !weeksData[sortedWeeksKeys[month]]) break;

                for (const w in weeksData[sortedWeeksKeys[month]]) {
                    vente.push(weeksData[sortedWeeksKeys[month]][w].vente);
                    argent.push(weeksData[sortedWeeksKeys[month]][w].argent);
                }

                chart = new Chart(canva, weekChart(vente, argent, Object.keys(weeksData[sortedWeeksKeys[sortedWeeksKeys.length - 1]]) ?? ''));
                
                document.querySelector('article h3').innerHTML = getMonthLabel(month);

                maxIndex = sortedWeeksKeys.length - 1;

                document.getElementById('prev').disabled = index == maxIndex;
                document.getElementById('next').disabled = true;

                break;
            
            case 'Mensuel':
                const year = Object.keys(monthsData).length - 1;

                if (!Object.values(monthsData)[year]) break;

                for (const m in Object.values(monthsData)[year]) {
                    vente.push(Object.values(monthsData)[year][m].vente);
                    argent.push(Object.values(monthsData)[year][m].argent);
                }
                
                chart = new Chart(canva, monthChart(vente, argent));

                document.querySelector('article h3').innerHTML = Object.keys(monthsData)[year];

                maxIndex = Object.keys(monthsData).length - 1;

                document.getElementById('prev').disabled = index == maxIndex;
                document.getElementById('next').disabled = true;

                break;
            
            case 'Annuel':
                if (!Object.keys(yearsData).length) break;

                for (const y in yearsData) {
                    vente.push(yearsData[y].vente);
                    argent.push(yearsData[y].argent);
                }

                chart = new Chart(canva, yearChart(vente, argent, Object.keys(yearsData)));

                document.getElementById('prev').disabled = true;
                document.getElementById('next').disabled = true;

                document.querySelector('article h3').innerHTML = '';

                maxIndex = 0;

                break;
        }

        document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
        let total = argent.reduce((a, b) => a + b, 0);
        let formatted = Number.isInteger(total) ? total + '€' : total.toFixed(2) + '€';
        document.getElementById('argents').innerHTML = formatted;
    })
})

// Filtage par catégorie
document.getElementById('category').addEventListener('change', async e => {
    const category = e.target.value;

    // Modification du sélecteur de produit
    productsSelector.innerHTML = '<option value="" default>Aucun filtre de produit</option>';

    const products = await fetchData(`/api/products?category=${encodeURIComponent(category)}`);

    for (const product of products) {
        productsSelector.innerHTML += `<option value="${product['nom']}">${product['nom']}</option>`;
    }

    // Filtrage des données
    data = await fetchData(category ? `/api/stats?category=${encodeURIComponent(category)}` : '/api/stats');

    buildData(data);
    index = 0;

    if (!sortedDaysKeys.length) {
        chart.destroy();
        document.getElementById('ventes').innerHTML = 0;
        document.getElementById('argents').innerHTML = '0€';
        document.querySelector('article h3').innerHTML = 'Aucune donnée';
        updateButtonStates();
        return;
    }

    updateStats();
    updateButtonStates();
})

// Filtrage par produit
productsSelector.addEventListener('change', async e => {
    const category = e.target.value;
    const product = e.target.value;

    console.log(product);

    // Filtrage des données
    data = await fetchData(product ? `/api/stats?product=${product}` : (category ? `/api/stats?catgory${category}` : '/api/stats'));

    console.log(data);

    buildData(data);
    index = 0;

    if (!sortedDaysKeys.length) {
        chart.destroy();
        document.getElementById('ventes').innerHTML = 0;
        document.getElementById('argents').innerHTML = '0€';
        document.querySelector('article h3').innerHTML = 'Aucune donnée';
        updateButtonStates();
        return;
    }

    updateStats();
    updateButtonStates();
})