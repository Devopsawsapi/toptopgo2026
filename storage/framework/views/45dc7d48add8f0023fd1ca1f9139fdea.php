

<?php $__env->startSection('content'); ?>

<div class="max-w-3xl mx-auto">

    <!-- HEADER -->
    <div class="flex items-center gap-4 mb-8">
        <a href="<?php echo e(route('admin.profiles.index')); ?>"
           class="text-gray-400 hover:text-gray-700 transition text-2xl">←</a>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">👤 Détail du profil</h1>
            <p class="text-gray-500 text-sm mt-1">Informations complètes de l'administrateur</p>
        </div>
    </div>

    <!-- CARTE PROFIL -->
    <div class="bg-white rounded-2xl shadow-md p-8 mb-6">

        <div class="flex items-center gap-6 mb-8">
            <!-- Avatar -->
            <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl font-bold
                <?php echo e($admin->status === 'active' ? 'bg-[#1DA1F2] text-white' : 'bg-gray-300 text-gray-600'); ?>">
                <?php echo e(strtoupper(substr($admin->first_name, 0, 1))); ?>

            </div>

            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <?php echo e($admin->first_name); ?> <?php echo e($admin->last_name); ?>

                </h2>
                <p class="text-gray-500"><?php echo e($admin->email); ?></p>
                <div class="flex items-center gap-3 mt-2">
                    <!-- Badge rôle -->
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full">
                        <?php echo e($admin->role->name ?? '—'); ?>

                    </span>
                    <!-- Badge statut -->
                    <?php if($admin->status === 'active'): ?>
                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                            ✅ Actif
                        </span>
                    <?php else: ?>
                        <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">
                            🚫 Bloqué
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- DÉTAILS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Prénom</p>
                <p class="font-semibold text-gray-800"><?php echo e($admin->first_name); ?></p>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Nom</p>
                <p class="font-semibold text-gray-800"><?php echo e($admin->last_name); ?></p>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Email</p>
                <p class="font-semibold text-gray-800"><?php echo e($admin->email); ?></p>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Téléphone</p>
                <p class="font-semibold text-gray-800"><?php echo e($admin->phone ?? '—'); ?></p>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Rôle</p>
                <p class="font-semibold text-gray-800"><?php echo e($admin->role->name ?? '—'); ?></p>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Statut</p>
                <p class="font-semibold <?php echo e($admin->status === 'active' ? 'text-green-600' : 'text-red-600'); ?>">
                    <?php echo e($admin->status === 'active' ? 'Actif' : 'Bloqué'); ?>

                </p>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Date de création</p>
                <p class="font-semibold text-gray-800"><?php echo e($admin->created_at->format('d/m/Y à H:i')); ?></p>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Dernière modification</p>
                <p class="font-semibold text-gray-800"><?php echo e($admin->updated_at->format('d/m/Y à H:i')); ?></p>
            </div>

        </div>
    </div>

    <!-- ACTIONS -->
    <div class="flex gap-4">

        <a href="<?php echo e(route('admin.profiles.edit', $admin->id)); ?>"
           class="flex-1 bg-[#1DA1F2] text-white py-3 rounded-xl font-semibold text-center
                  hover:bg-[#FFC107] hover:text-black transition-all duration-300">
            ✏️ Modifier
        </a>

        <?php if($admin->id !== session('admin_id')): ?>
            <?php if($admin->status === 'active'): ?>
                <form method="POST" action="<?php echo e(route('admin.profiles.block', $admin->id)); ?>" class="flex-1">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                            onclick="return confirm('Bloquer <?php echo e($admin->first_name); ?> ?')"
                            class="w-full bg-orange-100 text-orange-700 py-3 rounded-xl font-semibold
                                   hover:bg-orange-200 transition">
                        🚫 Bloquer
                    </button>
                </form>
            <?php else: ?>
                <form method="POST" action="<?php echo e(route('admin.profiles.activate', $admin->id)); ?>" class="flex-1">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                            class="w-full bg-green-100 text-green-700 py-3 rounded-xl font-semibold
                                   hover:bg-green-200 transition">
                        ✅ Activer
                    </button>
                </form>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('admin.profiles.destroy', $admin->id)); ?>" class="flex-1">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit"
                        onclick="return confirm('Supprimer définitivement <?php echo e($admin->first_name); ?> ?')"
                        class="w-full bg-red-100 text-red-700 py-3 rounded-xl font-semibold
                               hover:bg-red-200 transition">
                    🗑 Supprimer
                </button>
            </form>
        <?php endif; ?>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/profiles/show.blade.php ENDPATH**/ ?>