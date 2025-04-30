<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold">Admin Dashboard</h1>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <button id="logoutBtn" class="ml-4 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div id="dashboard-content" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Dashboard content will be loaded here -->
                <div class="animate-pulse bg-white shadow rounded-lg p-4">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="space-y-3 mt-4">
                        <div class="h-4 bg-gray-200 rounded"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Check if token exists
        const token = localStorage.getItem('admin_token');
        if (!token) {
            window.location.href = '/admin/login';
        }

        // Set token for all requests
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        // Load dashboard data
        async function loadDashboard() {
            try {
                const response = await axios.get('/api/admins/dashboard');
                const data = response.data;
                
                // Create dashboard content
                const content = document.getElementById('dashboard-content');
                content.innerHTML = `
                    <div class="bg-white shadow rounded-lg p-4">
                        <h2 class="text-lg font-semibold mb-4">Users</h2>
                        <p>Total: ${data.statistics.users.total}</p>
                        <p>New Today: ${data.statistics.users.new_today}</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <h2 class="text-lg font-semibold mb-4">Products</h2>
                        <p>Total: ${data.statistics.products.total}</p>
                        <p>Pending: ${data.statistics.products.pending}</p>
                        <p>Approved: ${data.statistics.products.approved}</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <h2 class="text-lg font-semibold mb-4">Orders</h2>
                        <p>Total: ${data.statistics.orders.total}</p>
                        <p>Pending: ${data.statistics.orders.pending}</p>
                        <p>Completed: ${data.statistics.orders.completed}</p>
                    </div>
                    <div class="bg-white shadow rounded-lg p-4">
                        <h2 class="text-lg font-semibold mb-4">Revenue</h2>
                        <p>Total: $${data.statistics.revenue.total}</p>
                        <p>Today: $${data.statistics.revenue.today}</p>
                    </div>
                `;
            } catch (error) {
                if (error.response?.status === 401) {
                    // Token expired or invalid
                    localStorage.removeItem('admin_token');
                    window.location.href = '/admin/login';
                } else {
                    // Show error message in dashboard
                    const content = document.getElementById('dashboard-content');
                    content.innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">${error.response?.data?.message || 'Failed to load dashboard data'}</span>
                        </div>
                    `;
                }
            }
        }

        // Handle logout
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            try {
                await axios.post('/api/admins/logout');
                localStorage.removeItem('admin_token');
                window.location.href = '/admin/login';
            } catch (error) {
                console.error('Logout failed:', error);
            }
        });

        // Load dashboard on page load
        loadDashboard();
    </script>
</body>
</html> 