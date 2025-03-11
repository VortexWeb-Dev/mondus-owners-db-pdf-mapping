<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mondus - Owner's DB PDF Mapping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<body class="bg-gray-50 min-h-screen font-sans antialiased">
    <!-- Main Container -->
    <div class="max-w-[80vw] mx-auto p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Mondus</h1>
                <p class="text-sm text-gray-500">Owner's DB PDF Mapping</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow-sm p-6 relative">
            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="absolute inset-0 z-50 bg-gray-100 bg-opacity-75 flex items-center justify-center rounded-lg hidden">
                <div class="w-8 h-8 border-4 border-gray-900 border-t-transparent rounded-full animate-spin"></div>
            </div>

            <!-- Table -->
            <div class="relative overflow-x-auto">
                <table id="itemsTable" class="w-full text-sm text-left text-gray-700">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">ID</th>
                            <th scope="col" class="px-6 py-3">Title</th>
                            <th scope="col" class="px-6 py-3">Emirate</th>
                            <th scope="col" class="px-6 py-3">Building Name</th>
                            <th scope="col" class="px-6 py-3">Address</th>
                            <th scope="col" class="px-6 py-3">Property Type</th>
                            <th scope="col" class="px-6 py-3">Listing Type</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Asking/Renting Price</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody"></tbody>
                </table>
                <div id="pagination" class="mt-4 flex items-center justify-between"></div>
            </div>

        </div>

        <!-- Footer -->
        <footer class="mt-8 text-center text-sm text-gray-500">
            © <a href="http://vortexweb.org" class="hover:underline">VortexWeb</a> <?php echo date('Y'); ?>
        </footer>
    </div>

    <!-- Toast -->
    <div id="toastContainer" class="fixed top-4 right-4 space-y-2 z-50"></div>

    <!-- JavaScript -->
    <script src="script.js" type="module">
    </script>
</body>

</html>