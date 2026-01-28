<script setup>
import { computed, ref } from "vue";
import dataservices from "./services/dataservices";

const city = ref("");
const country = ref("");
const units = ref("metric");

const loading = ref(false);
const error = ref("");
const weather = ref(null);

const unitsSymbol = computed(() => (units.value === "metric" ? "°C" : "°F"));
const windUnit = computed(() => (units.value === "metric" ? "m/s" : "mph"));

async function fetchWeather() {
  error.value = "";
  weather.value = null;

  if (!city.value || !country.value) {
    error.value = "Please enter both city and country.";
    return;
  }

  loading.value = true;
  try {
    const qs = new URLSearchParams({
      city: city.value,
      country: country.value,
      units: units.value,
    });

    const res = await dataservices.show_weather();
    const json = await res.json();

    if (!res.ok) {
      error.value = json?.message || "Failed to fetch weather.";
      return;
    }

    // keep same shape
    weather.value = { data: json.data, location: json.data.location };
  } catch (e) {
    error.value = e?.message || "Network error.";
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="container">
    <h1>Weather Checker</h1>

    <form @submit.prevent="fetchWeather" class="card">
      <div class="row">
        <input v-model.trim="city" placeholder="City (e.g., Dar es Salaam)" />
        <input v-model.trim="country" placeholder="Country (e.g., TZ)" />
        <select v-model="units">
          <option value="metric">Metric (°C)</option>
          <option value="imperial">Imperial (°F)</option>
        </select>
      </div>

      <button :disabled="loading">
        {{ loading ? "Checking..." : "Check Weather" }}
      </button>

      <p v-if="error" class="error">{{ error }}</p>
    </form>

    <div v-if="weather" class="card">
      <h2>
        {{ weather.location.city }}, {{ weather.location.country }}
      </h2>

      <p class="desc">
        {{ weather.data.weather.description }}
      </p>

      <div class="grid">
        <div class="box">
          <strong>Temperature</strong>
          <div>{{ weather.data.weather.temp }} {{ unitsSymbol }}</div>
        </div>

        <div class="box">
          <strong>Humidity</strong>
          <div>{{ weather.data.weather.humidity }}%</div>
        </div>

        <div class="box">
          <strong>Wind Speed</strong>
          <div>{{ weather.data.weather.wind_speed }} {{ windUnit }}</div>
        </div>
      </div>
    </div>
  </div>
</template>



<style scoped>
.container { max-width: 900px; margin: 20px auto; padding: 16px; }
.card { background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 16px; margin-top: 14px; }
.row { display: grid; grid-template-columns: 1fr 1fr 220px; gap: 10px; }
input, select { padding: 10px; border-radius: 10px; border: 1px solid #ddd; width: 100%; }
button { margin-top: 10px; padding: 10px 14px; border-radius: 10px; border: none; cursor: pointer; }
.error { color: #b00020; margin-top: 10px; }
.grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px; }
.box { padding: 12px; border: 1px solid #eee; border-radius: 10px; }
@media (max-width: 800px) {
  .row { grid-template-columns: 1fr; }
  .grid { grid-template-columns: 1fr; }
}
</style>
