// Using Chart.js
import Chart from "chart.js/auto";

document.addEventListener("DOMContentLoaded", () => {
  let trainingChart = null;
  let gameChart = null;

  const createChart = (elementId, labels) => {
    return new Chart(document.getElementById(elementId), {
      type: "doughnut",
      data: {
        labels: labels,
        datasets: [
          {
            data: Array(labels.length).fill(0),
            hoverOffset: 20,
          },
        ],
      },
      options: {
        plugins: {
          legend: {
            position: "bottom",
          },
        },
      },
    });
  };

  const updateChartData = (chart, data) => {
    const labels = Object.keys(data);
    const values = Object.values(data);
    chart.data.labels = labels;
    chart.data.datasets[0].data = values;
    chart.update();
  };

  const localeContainer = document.querySelector(".locale-container");
  const locale = localeContainer.dataset.locale;

  const fetchData = (chart, category, year) =>
    fetch(`/${locale}/statistics/api/${category}/${year.value}`)
      .then((response) => response.json())
      .then((data) => {
        updateChartData(chart, data);
      })
      .catch((error) => {
        console.error(error.message);
      });

  const defaultLabels = ["Présent", "Absent", "Pas de réponse"];
  trainingChart = createChart("trainingStat", defaultLabels);
  gameChart = createChart("gameStat", defaultLabels);

  const yearTraining = document.getElementById("yearTrainingSelect");
  if (yearTraining) {
    yearTraining.addEventListener("change", () => {
      fetchData(trainingChart, "training", yearTraining);
    });

    fetchData(trainingChart, "training", yearTraining);
  }

  const yearGame = document.getElementById("yearGameSelect");
  if (yearGame) {
    yearGame.addEventListener("change", () => {
      fetchData(gameChart, "game", yearGame);
    });

    fetchData(gameChart, "game", yearGame);
  }
});
