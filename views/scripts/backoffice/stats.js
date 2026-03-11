import { Chart} from 'https://cdn.jsdelivr.net/npm/chart.js/auto/+esm';
import { dayChart, weekChart, monthChart, yearChart } from './charts.js';
import moment from 'https://cdn.jsdelivr.net/npm/moment/+esm';

const data = await fetch('/controllers/api.php?action=stats').then(res => res.json());

const months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

const daysData = {};
const weeksData = {};
const monthsData = {};
const yearsData = {};

let selected = document.querySelector('.selected');

let index = 0;
let maxIndex = 0;

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
        };
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
    const yea = moment(d.dataCommande).year();
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

let [vente, argent] = [[], []];
let week = Object.keys(daysData).length - 1;
for (const d in Object.values(daysData)[week]) {
    vente.push(Object.values(daysData)[week][d].vente);
    argent.push(Object.values(daysData)[week][d].argent);
}

document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
let total = argent.reduce((a, b) => a + b, 0);
let formatted = Number.isInteger(total) ? total + '€' : total.toFixed(2) + '€';
document.getElementById('argents').innerHTML = formatted;

const canva = document.getElementById('stats');
let chart = new Chart(canva, dayChart(vente, argent));

document.getElementById('prev').disabled = Object.keys(daysData).length == 1 ? true : false;

function getWeekLabel(week) {
    if (!week) return '';
    return `Semaine du ${moment().isoWeek(week.split('/')[0]).startOf('isoWeek').format('DD/MM')} au ${moment().isoWeek(week.split('/')[0]).startOf('isoWeek').add(6, 'days').format('DD/MM')}`;
}

function getMonthLabel(month) {
    if (isNaN(month)) return;

    console.log(month);
    console.log(moment(Object.keys(weeksData)[month], 'MM/YYYY').month());
    console.log(months[moment(Object.keys(weeksData)[month], 'MM/YYYY').month()])

    return `${months[moment(Object.keys(weeksData)[month], 'MM/YYYY').month()]} ${moment(Object.keys(weeksData)[month], 'MM/YYYY').year()}`;
}

document.querySelector('article h3').innerHTML = getWeekLabel(Object.keys(daysData)[week]);

function updateStats() {
    chart.destroy();
    [vente, argent] = [[], []];

    switch (selected.innerHTML) {
        case 'Journalier':
            week = Object.keys(daysData).length - 1 - index;
            for (const d in Object.values(daysData)[week]) {
                vente.push(Object.values(daysData)[week][d].vente);
                argent.push(Object.values(daysData)[week][d].argent);
            }

            document.querySelector('article h3').innerHTML = getWeekLabel(Object.keys(daysData)[week]);

            chart = new Chart(canva, dayChart(vente, argent));
            break;
        
        case 'Hebdomadaire':
            const month = Object.keys(weeksData).length - 1 - index;
            for (const w in Object.values(weeksData)[month]) {
                vente.push(Object.values(weeksData)[month][w].vente);
                argent.push(Object.values(weeksData)[month][w].argent);
            }

            document.querySelector('article h3').innerHTML = getMonthLabel(month);

            chart = new Chart(canva, weekChart(vente, argent, Object.keys(weeksData[Object.keys(weeksData)[month]]) ?? ''));

            break;
    }

    document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
    total = argent.reduce((a, b) => a + b, 0);
    formatted = Number.isInteger(total) ? total + '€' : total.toFixed(2) + '€';
    document.getElementById('argents').innerHTML = formatted;
}

maxIndex = Object.keys(daysData).length - 1;

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
                week = Object.keys(daysData).length - 1;
                for (const d in Object.values(daysData)[week]) {
                    vente.push(Object.values(daysData)[week][d].vente);
                    argent.push(Object.values(daysData)[week][d].argent);
                }

                chart = new Chart(canva, dayChart(vente, argent));

                document.querySelector('article h3').innerHTML = getWeekLabel(Object.keys(daysData)[week]);

                maxIndex = Object.keys(daysData).length - 1;

                document.getElementById('prev').disabled = index == maxIndex;
                document.getElementById('next').disabled = true;

                break;

            case 'Hebdomadaire':
                const month = Object.keys(weeksData).length - 1;
                for (const w in Object.values(weeksData)[month]) {
                    vente.push(Object.values(weeksData)[month][w].vente);
                    argent.push(Object.values(weeksData)[month][w].argent);
                }

                chart = new Chart(canva, weekChart(vente, argent, Object.keys(weeksData[Object.keys(weeksData)[Object.keys(weeksData).length - 1]]) ?? ''));
                
                document.querySelector('article h3').innerHTML = getMonthLabel(month);

                maxIndex = Object.keys(weeksData).length - 1;

                document.getElementById('prev').disabled = index == maxIndex;
                document.getElementById('next').disabled = true;

                break;
            
            case 'Mensuel':
                [vente, argent] = [[12, 19, 3, 5, 2, 3, 2, 8, 4, 1, 2, 0], [45, 41, 21, 45, 13, 5, 13, 20, 14, 13, 10, 0]];
                chart = new Chart(canva, monthChart(vente, argent));

                document.getElementById('prev').disabled = false;
                document.getElementById('next').disabled = true;
                break;
            
            case 'Annuel':
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