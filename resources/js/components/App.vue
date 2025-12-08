<template>
  <div class="app">
    <!-- Drawer / sidebar -->
    <Drawer @navigate="currentPage = $event" />

    <!-- Main content -->
    <main class="content">
      <component :is="currentPage" />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Drawer from './components/Drawer.vue'

// Import pages (these will be top-level components)
import SalesTable from './components/Sales/SalesTable.vue'
import PurchasesTable from './components/Purchases/PurchasesTable.vue'

const currentPage = ref(SalesTable) // default page

// Optional: a mapping if using event names from Drawer
const pages = {
  sales: SalesTable,
  purchases: PurchasesTable
}
</script>

<style>
.app {
  display: flex;
  height: 100vh;
  overflow: hidden;
}
.content {
  flex: 1;
  background-color: #f4f4f4;
  padding: 20px;
  overflow-y: auto;
}
</style>