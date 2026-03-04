const options = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: {
            stacked: true,
            ticks: {
                font: {
                    weight: 'bold',
                    size: 20
                }
            },
            grid: {
                display: false
            }
        },
        y: {
            stacked: false,
            beginAtZero: true,
            grid: {
                display: false
            }
        }
    },
    plugins: {
        legend: {
            labels: {
                font: {
                    size: 20,
                    weight: 'bold'
                }
            },
            
            onHover: () => {
                document.body.style.cursor = 'pointer';
            },
            
            onLeave: () => {
                document.body.style.cursor = 'default';
            }
        }
    }
}

const dayChart = (vente, argent, days) => ({
    type: 'bar',
    
    data: {
        labels: days,
        datasets: [
            {
                label: 'Nombre de Ventes',
                data: vente,
                stack: 'same',
                borderWidth: 3,
                borderRadius: 10,
                backgroundColor: '#e3f2fe',
                borderColor: '#273469'
            }, {
                label: 'Chiffre d\'Affaires',
                data: argent,
                stack: 'same',
                borderWidth: 3,
                borderRadius: 16,
                backgroundColor: '#273469',
                borderColor: 'rgba(0, 0, 0, 0)'
            }
        ]
    },
    
    options: options
})

const weekChart = (vente, argent) => ({
    type: 'bar',
    
    data: {
        labels: ['1', '2', '3', '4', '5'],
        datasets: [
            {
                label: 'Nombre de Ventes',
                data: vente,
                stack: 'same',
                borderWidth: 3,
                borderRadius: 10,
                backgroundColor: '#e3f2fe',
                borderColor: '#273469'
            }, {
                label: 'Chiffre d\'Affaires',
                data: argent,
                stack: 'same',
                borderWidth: 3,
                borderRadius: 16,
                backgroundColor: '#273469',
                borderColor: 'rgba(0, 0, 0, 0)'
            }
        ]
    },
    
    options: options
})

const monthChart = (vente, argent) => ({
    type: 'bar',
    
    data: {
        labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        datasets: [
            {
                label: 'Nombre de Ventes',
                data: vente,
                stack: 'same',
                borderWidth: 3,
                borderRadius: 10,
                backgroundColor: '#e3f2fe',
                borderColor: '#273469'
            }, {
                label: 'Chiffre d\'Affaires',
                data: argent,
                stack: 'same',
                borderWidth: 3,
                borderRadius: 16,
                backgroundColor: '#273469',
                borderColor: 'rgba(0, 0, 0, 0)'
            }
        ]
    },
    
    options: options
})

const yearChart = (vente, argent, years) => ({
    type: 'bar',
    
    data: {
        labels: years,
        datasets: [
            {
                label: 'Nombre de Ventes',
                data: vente,
                stack: 'same',
                borderWidth: 3,
                borderRadius: 10,
                backgroundColor: '#e3f2fe',
                borderColor: '#273469'
            }, {
                label: 'Chiffre d\'Affaires',
                data: argent,
                stack: 'same',
                borderWidth: 3,
                borderRadius: 16,
                backgroundColor: '#273469',
                borderColor: 'rgba(0, 0, 0, 0)'
            }
        ]
    },
    
    options: options
})

export { dayChart, weekChart, monthChart, yearChart };