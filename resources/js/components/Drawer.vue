<template>
  <div :class="['sidebar', { open }]">
    <div class="header">
      <span class="toggle" @click="toggleDrawer">☰</span>
      <span v-if="open" class="title">Menu</span>
    </div>

    <!-- Sales menu -->
    <div class="menu-item" @click="toggleSubmenu('sales')">
      <i>💰</i><span v-if="open">Sales</span>
    </div>
    <div v-show="submenu.sales" class="submenu">
      <a @click="$emit('navigate', pages.sales)">Sales Table</a>
      <a @click="$emit('navigate', pages.sales)">Add Sale</a>
    </div>

    <!-- Purchases menu -->
    <div class="menu-item" @click="toggleSubmenu('purchases')">
      <i>🛒</i><span v-if="open">Purchases</span>
    </div>
    <div v-show="submenu.purchases" class="submenu">
      <a @click="$emit('navigate', pages.purchases)">Purchases Table</a>
      <a @click="$emit('navigate', pages.purchases)">Add Purchase</a>
    </div>
  </div>
</template>

<script setup>
import SalesTable from './Sales/SalesTable.vue'
import PurchasesTable from './Purchases/PurchasesTable.vue'
import { ref } from 'vue'

const open = ref(false)
const submenu = ref({ sales: false, purchases: false })

function toggleDrawer() {
  open.value = !open.value
}

function toggleSubmenu(name) {
  submenu.value[name] = !submenu.value[name]
}

// Map links to components
const pages = {
  sales: SalesTable,
  purchases: PurchasesTable
}
</script>

<style scoped>
.sidebar {
  background: #2c3e50;
  color: white;
  width: 70px;
  transition: width 0.3s;
  overflow: hidden;
  height: 100vh;
}
.sidebar.open {
  width: 220px;
}
.header {
  background: #1a252f;
  padding: 15px;
  display: flex;
  align-items: center;
}
.toggle {
  cursor: pointer;
  font-size: 20px;
}
.title {
  margin-left: 10px;
  font-weight: bold;
}
.menu-item {
  padding: 12px 15px;
  display: flex;
  align-items: center;
  cursor: pointer;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}
.menu-item:hover {
  background: #34495e;
}
.menu-item i {
  width: 30px;
  text-align: center;
}
.submenu {
  background: #34495e;
}
.submenu a {
  display: block;
  padding: 10px 45px;
  color: #ecf0f1;
  text-decoration: none;
  font-size: 14px;
}
.submenu a:hover {
  background: #3b566e;
}
</style>