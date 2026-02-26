<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>TopTopGo Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- ================= SIDEBAR ================= -->
    <aside class="w-72 bg-black text-white flex flex-col shadow-2xl">

        <!-- LOGO -->
        <div class="flex justify-center items-center py-6 border-b border-gray-800">
            <img src="<?php echo e(asset('images/logo4.png')); ?>"
                 class="w-48 h-auto object-contain">
        </div>

        <!-- MENU -->
        <nav class="flex-1 p-4 space-y-2 text-sm overflow-y-auto">

            <a href="<?php echo e(route('admin.dashboard')); ?>"
               class="flex items-center px-4 py-2 rounded-lg
               hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300
               <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-[#1DA1F2] pl-6' : ''); ?>">
                📊 Dashboard
            </a>

            <!-- MESSAGERIE -->
            <p class="text-xs text-gray-400 mt-6 uppercase tracking-wider">
                Messagerie
            </p>

            <a href="<?php echo e(route('admin.messages.index')); ?>"
               class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300
               <?php echo e(request()->routeIs('admin.messages.*') ? 'bg-[#1DA1F2] pl-6' : ''); ?>">
                💬 Users ↔ Chauffeurs
            </a>

            <a href="<?php echo e(route('admin.support.users.index')); ?>"
               class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300
               <?php echo e(request()->routeIs('admin.support.users.*') ? 'bg-[#1DA1F2] pl-6' : ''); ?>">
                🛡 Admin ↔ Utilisateurs
            </a>

            
            <a href="<?php echo e(route('admin.support.drivers.index')); ?>"
               class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300
               <?php echo e(request()->routeIs('admin.support.drivers.*') ? 'bg-[#1DA1F2] pl-6' : ''); ?>">
                🛡 Admin ↔ Chauffeurs
            </a>

            <!-- GESTION -->
            <p class="text-xs text-gray-400 mt-6 uppercase tracking-wider">
                Gestion
            </p>

            <?php
                try {
                    $pendingKyc = \App\Models\Driver\Driver::where('status', 'pending')->count();
                } catch (\Exception $e) {
                    $pendingKyc = 0;
                }
            ?>

            <a href="<?php echo e(route('admin.drivers.index')); ?>"
               class="flex justify-between items-center px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300
               <?php echo e(request()->routeIs('admin.drivers.*') ? 'bg-[#1DA1F2] pl-6' : ''); ?>">
                <span>🚗 Chauffeurs</span>
                <?php if($pendingKyc > 0): ?>
                    <span class="bg-red-600 text-xs px-2 py-1 rounded-full">
                        <?php echo e($pendingKyc); ?>

                    </span>
                <?php endif; ?>
            </a>

            <a href="<?php echo e(route('admin.users.index')); ?>"
               class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300
               <?php echo e(request()->routeIs('admin.users.*') ? 'bg-[#1DA1F2] pl-6' : ''); ?>">
                👤 Clients
            </a>

            <a href="<?php echo e(route('admin.profiles.index')); ?>"
               class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300
               <?php echo e(request()->routeIs('admin.profiles.*') ? 'bg-[#1DA1F2] pl-6' : ''); ?>">
                👤 Gestion Des Administrateurs
            </a>

            <!-- FINANCES -->
            <p class="text-xs text-gray-400 mt-6 uppercase tracking-wider">
                Finances
            </p>

            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300">
                💰 Revenus
            </a>

            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300">
                📊 Commissions
            </a>

            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300">
                💱 Devises
            </a>

            <!-- LOCALISATION -->
            <p class="text-xs text-gray-400 mt-6 uppercase tracking-wider">
                Localisation
            </p>

            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300">
                📍 Géolocalisation Live
            </a>

            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300">
                🌍 Pays
            </a>

            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-[#1DA1F2] hover:pl-6 transition-all duration-300">
                🏙️ Villes
            </a>

        </nav>

        <!-- PROFIL -->
        <div class="p-4 border-t border-gray-800 bg-gray-900">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-[#FFC107] rounded-full flex items-center justify-center text-black font-bold">
                    <?php echo e(strtoupper(substr(session('admin_name', 'A'), 0, 1))); ?>

                </div>
                <div>
                    <p class="text-sm font-semibold">
                        <?php echo e(session('admin_name', 'Admin')); ?>

                    </p>
                    <p class="text-xs text-gray-400">Super Admin</p>
                </div>
            </div>

            <form method="POST" action="<?php echo e(route('admin.logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full bg-[#FFC107] text-black py-2 rounded-lg font-semibold
                           hover:bg-[#1DA1F2] hover:text-white
                           transition-all duration-300">
                    Déconnexion
                </button>
            </form>
        </div>

    </aside>

    <!-- ================= CONTENT ================= -->
    <div class="flex-1 flex flex-col">

        <main class="flex-1 p-8">

            <!-- Toast container -->
            <div id="toast-container" class="fixed top-5 right-5 z-50 space-y-3"></div>

            <?php if(session('success')): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        showToast("<?php echo e(session('success')); ?>", "success");
                    });
                </script>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        showToast("<?php echo e(session('error')); ?>", "error");
                    });
                </script>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>

        </main>

        <footer class="bg-white border-t py-4">
            <p class="text-center text-gray-500 text-sm">
                © <?php echo e(date('Y')); ?> TopTopGo. Développé avec ❤️ par
                <span class="font-bold text-gray-700">Basile NGASSAKI</span>
            </p>
        </footer>

    </div>

</div>

<!-- Leaflet -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `
        px-5 py-3 rounded-lg shadow-lg text-white
        transform transition-all duration-300 translate-x-20 opacity-0
        ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}
    `;
    toast.innerText = message;
    document.getElementById("toast-container").appendChild(toast);
    setTimeout(() => { toast.classList.remove("translate-x-20","opacity-0"); }, 100);
    setTimeout(() => {
        toast.classList.add("opacity-0");
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

function previewImage(event, previewId) {
    const reader = new FileReader();
    reader.onload = function(){
        const img = document.getElementById(previewId);
        img.src = reader.result;
        img.classList.remove('hidden');
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/layouts/app.blade.php ENDPATH**/ ?>