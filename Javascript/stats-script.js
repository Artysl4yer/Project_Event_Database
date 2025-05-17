const ctx = document.getElementById('myChart');

fetch("../php/stats-script.php")

.then((response) => {
    return response.json();
})
.then((data) => {
    createChart(data, 'bar')
});

function createChart(chartData, type){
  new Chart(ctx, {
    type: type,
    data: {
      labels: chartData.map(row => row.date),
      datasets: [{
        label:  '  ',
        data: chartData.map(row => row.income),
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