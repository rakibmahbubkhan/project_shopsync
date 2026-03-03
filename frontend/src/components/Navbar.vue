<template>
  <header class="bg-white border-b h-16 flex items-center justify-between px-4 sm:px-6 shadow-sm z-10">
    <div class="flex items-center">
      <button class="md:hidden p-2 mr-2 text-gray-600 hover:bg-gray-100 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
      </button>
      <h2 class="font-bold text-gray-800 hidden sm:block">Overview</h2>
    </div>

    <div class="flex items-center space-x-2 sm:space-x-4">
      <div class="text-right hidden sm:block">
        <p class="text-xs font-bold text-gray-800">Administrator</p>
        <p class="text-[10px] text-gray-500">Workshop Manager</p>
      </div>
      <button class="p-2 bg-gray-50 rounded-full hover:bg-red-50 group transition-colors">
        <span class="text-sm font-medium text-gray-600 group-hover:text-red-600">Logout</span>
      </button>
    </div>
  </header>
</template>