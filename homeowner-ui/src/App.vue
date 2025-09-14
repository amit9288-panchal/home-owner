<template>
  <div class="container">
    <h2 class="app-header">
      <span class="emoji">🏡</span> {{ appName }} CSV Parser
    </h2>

    <form @submit.prevent="uploadCsv" class="upload-form">
      <label for="csv">Upload CSV:</label>
      <input type="file" id="csv" @change="handleFile" accept=".csv" />
      <button type="submit" :disabled="!csvFile">Upload</button>
    </form>

    <div class="search-bar">
      <label for="search">🔍 Search:</label>
      <input id="search" v-model="search" @input="fetchPeople" placeholder="Enter name..." />
    </div>

    <table v-if="Array.isArray(people) && people.length" class="results-table">
      <thead>
      <tr>
        <th>Title</th>
        <th>First Name</th>
        <th>Initial</th>
        <th>Last Name</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="person in people" :key="person.id">
        <td>{{ person.title }}</td>
        <td>{{ person.first_name || '-' }}</td>
        <td>{{ person.initial || '-' }}</td>
        <td>{{ person.last_name }}</td>
      </tr>
      </tbody>
    </table>

    <p v-else>No results found.</p>

    <div v-if="toast.message" :class="['toast', toast.type]">
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const people = ref([])
const search = ref('')
const csvFile = ref(null)
const toast = ref({ message: '', type: '' })

const appName = import.meta.env.VITE_APP_NAME || 'Homeowner'
const API_TOKEN = import.meta.env.VITE_API_TOKEN
const API_PREFIX = '/api/v1'

function showToast(message, type = 'success') {
  toast.value = { message, type }
  setTimeout(() => {
    toast.value = { message: '', type: '' }
  }, 3000)
}

function handleFile(e) {
  csvFile.value = e.target.files[0]
}

function uploadCsv() {
  const formData = new FormData()
  formData.append('csv', csvFile.value)

  fetch(`${API_PREFIX}/upload`, {
    method: 'POST',
    headers: {
      'X-Auth-Token': API_TOKEN
    },
    body: formData
  })
      .then(res => res.json())
      .then(res => {
        if (res.success) {
          csvFile.value = null
          document.getElementById('csv').value = ''
          fetchPeople()
          showToast(res.message || 'Upload successful', 'success')
        } else {
          showToast(res.message || 'Upload failed', 'error')
        }
      })
      .catch(err => {
        showToast('Upload error: ' + err.message, 'error')
      })
}

function fetchPeople() {
  fetch(`${API_PREFIX}/people?search=${search.value}`, {
    headers: {
      'X-Auth-Token': API_TOKEN
    }
  })
      .then(res => res.json())
      .then(res => {
        console.log('Fetch response:', res)
        if (res.success && Array.isArray(res.data)) {
          people.value = res.data
        } else {
          people.value = []
          showToast(res.message || 'Unexpected response', 'error')
        }
      })
      .catch(err => {
        people.value = []
        showToast('Fetch error: ' + err.message, 'error')
      })
}

onMounted(fetchPeople)
</script>

<style>
body {
  font-family: 'Inter', 'Segoe UI', sans-serif;
  background-color: #f9f9f9;
  color: #333;
}

.container {
  max-width: 800px;
  margin: auto;
  padding: 2rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
  margin-top: 2rem;
}

.app-header {
  font-size: 1.75rem;
  font-weight: 600;
  text-align: center;
  margin-bottom: 2rem;
  color: #0077cc;
}

.emoji {
  font-size: 2rem;
  margin-right: 0.5rem;
}

.upload-form {
  background: #f0f8ff;
  padding: 1rem;
  border-radius: 6px;
  border: 1px solid #cce5ff;
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1.5rem;
}

.upload-form input[type="file"] {
  flex: 1;
  border: 1px solid #ccc;
  padding: 0.4rem;
  border-radius: 4px;
}

.upload-form button {
  padding: 0.5rem 1rem;
  background-color: #0077cc;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.upload-form button:hover:not(:disabled) {
  background-color: #005fa3;
}

.upload-form button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.search-bar {
  background: #fff;
  border: 1px solid #ddd;
  padding: 0.75rem;
  border-radius: 6px;
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1.5rem;
}

.search-bar input {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.results-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

.results-table th,
.results-table td {
  border: 1px solid #ddd;
  padding: 0.75rem;
  text-align: left;
}

.results-table th {
  background-color: #f4f4f4;
}

.results-table tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

.results-table tbody tr:hover {
  background-color: #e6f7ff;
}

.toast {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  padding: 0.75rem 1.25rem;
  border-radius: 4px;
  color: white;
  font-weight: bold;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
  z-index: 1000;
  animation: slide-in 0.3s ease-out;
}

.toast.success {
  background-color: #28a745;
}

.toast.error {
  background-color: #dc3545;
}

@keyframes slide-in {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>
