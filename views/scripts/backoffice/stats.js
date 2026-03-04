import { Chart} from 'https://cdn.jsdelivr.net/npm/chart.js/auto/+esm';
import { dayChart, weekChart, monthChart, yearChart } from './charts.js';
import moment from 'https://cdn.jsdelivr.net/npm/moment/+esm';

const data = await fetch('/controllers/api.php?action=stats').then(res => res.json());

console.log(data);


const yearsData = {};

for (const d of data) {
    const year = moment(d.date).year();
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

console.log(yearsData);

let [vente, argent] = [[12, 19, 3, 5, 2, 3, 2], [45, 41, 21, 45, 13, 5, 13]];

document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
document.getElementById('argents').innerHTML = argent.reduce((a, b) => a + b, 0) + '€';

const canva = document.getElementById('stats');
let chart = new Chart(canva, dayChart(vente, argent));

document.querySelectorAll('button:not(#prev, #next)').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelector('.selected:not(#prev, #next)').classList.remove('selected');
        btn.classList.add('selected');

        const selected = document.querySelector('.selected');

        chart.destroy();

        switch (selected.innerHTML) {
            case 'Journalier':
                [vente, argent] = [[12, 19, 3, 5, 2, 3, 2], [45, 41, 21, 45, 13, 11, 13]];
                chart = new Chart(canva, dayChart(vente, argent));
                document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
                document.getElementById('argents').innerHTML = argent.reduce((a, b) => a + b, 0) + '€';

                document.getElementById('prev').style.display = 'block';
                document.getElementById('next').style.display = 'block';
                break;

            case 'Hebdomadaire':
                [vente, argent] = [[8, 5, 2, 3, 2], [21, 45, 13, 8, 13]];
                chart = new Chart(canva, weekChart(vente, argent));
                document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
                document.getElementById('argents').innerHTML = argent.reduce((a, b) => a + b, 0) + '€';

                document.getElementById('prev').disabled = false;
                document.getElementById('next').disabled = true;
                break;
            
            case 'Mensuel':
                [vente, argent] = [[12, 19, 3, 5, 2, 3, 2, 8, 4, 1, 2, 0], [45, 41, 21, 45, 13, 5, 13, 20, 14, 13, 10, 0]];
                chart = new Chart(canva, monthChart(vente, argent));
                document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
                document.getElementById('argents').innerHTML = argent.reduce((a, b) => a + b, 0) + '€';

                document.getElementById('prev').disabled = false;
                document.getElementById('next').disabled = true;
                break;
            
            case 'Annuel':
                [vente, argent] = [[200, 187, 354], [541, 345, 1623]];
                chart = new Chart(canva, yearChart(vente, argent));
                document.getElementById('ventes').innerHTML = vente.reduce((a, b) => a + b, 0);
                document.getElementById('argents').innerHTML = argent.reduce((a, b) => a + b, 0) + '€';

                document.getElementById('prev').disabled = true;
                document.getElementById('next').disabled = true;
                break;
        }
    })
})