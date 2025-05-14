const ctx = document.getElementById('myChart');

fetch("script.ph")

.then((respinse)) => {
    
}

function createChart(chartData, type){
  new Chart(ctx, {
    type: type,
    data: {
      labels: chartData,map(row => row.date),
      datasets: [{
        label: '# of Votes',
        data: chartData,map(row => row.income),
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
}