<!-- Top Navbar -->
<header class="bg-white/90 backdrop-blur-sm shadow-sm border-b border-gray-200 px-4 py-4 md:px-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div>
                <h2 class="text-xl md:text-2xl font-semibold text-gray-800">Welcome back,                                                                                                      <?php echo $fullName; ?></h2>
                <p class="text-sm text-gray-600 mt-1">Ready for your community contribution today!</p>
                <p class="text-xs text-gray-500 mt-1"><?php echo $location; ?></p>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <button
                    class="bg-primary-100 text-primary-700 p-2 rounded-full hover:bg-primary-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-5 5v-5zM8.124 3.79c0-.619.501-1.12 1.12-1.12h5.512c.619 0 1.12.501 1.12 1.12v7.75H8.124V3.79z">
                        </path>
                    </svg>
                </button>
                <span
                    class="absolute -top-1 -right-1 bg-danger-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
            </div>
            <div
                class="w-8 h-8 bg-gradient-to-r from-primary-500 to-primary-600 rounded-full flex items-center justify-center shadow-lg"
                title="<?php echo $fullName; ?> (<?php echo $email; ?>)">
                <span class="text-white text-sm font-medium"><?php echo $initials; ?></span>
            </div>
            <button onclick="logout()" data-logout-btn
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200">
                <span class="logout-text">Logout</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
            </button>
        </div>
    </div>
</header>