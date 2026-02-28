

<?php $__env->startSection('content'); ?>

<!-- HEADER -->
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            👨‍💼 GESTION DES <span class="text-[#1DA1F2]">PROFILS ADMINISTRATEURS</span>
        </h1>
        <p class="text-gray-500 text-sm mt-1">Gérez les administrateurs de la plateforme</p>
    </div>
    <a href="<?php echo e(route('admin.profiles.create')); ?>"
       class="bg-[#1DA1F2] text-white px-6 py-3 rounded-xl font-semibold
              hover:bg-[#FFC107] hover:text-black transition-all duration-300
              hover:-translate-y-1 hover:shadow-lg flex items-center gap-2">
        ➕ Nouvel Admin
    </a>
</div>

<!-- STATS RAPIDES -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-blue-500">
        <p class="text-gray-500 text-sm">Total Admins</p>
        <h2 class="text-3xl font-bold text-blue-500 mt-1"><?php echo e($admins->count()); ?></h2>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-green-500">
        <p class="text-gray-500 text-sm">Actifs</p>
        <h2 class="text-3xl font-bold text-green-500 mt-1">
            <?php echo e($admins->where('status', 'active')->count()); ?>

        </h2>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-md border-l-4 border-red-500">
        <p class="text-gray-500 text-sm">Bloqués</p>
        <h2 class="text-3xl font-bold text-red-500 mt-1">
            <?php echo e($admins->where('status', 'inactive')->count()); ?>

        </h2>
    </div>

</div>

<!-- TABLEAU -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <div class="p-6 border-b border-gray-100">
        <h2 class="text-lg font-bold text-gray-700">Liste des administrateurs</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4 text-left">Administrateur</th>
                    <th class="px-6 py-4 text-left">Email</th>
                    <th class="px-6 py-4 text-left">Téléphone</th>
                    <th class="px-6 py-4 text-left">Rôle</th>
                    <th class="px-6 py-4 text-left">Statut</th>
                    <th class="px-6 py-4 text-left">Créé le</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50 transition">
                    <!-- Nom -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm
                                <?php echo e($admin->status === 'active' ? 'bg-[#1DA1F2] text-white' : 'bg-gray-300 text-gray-600'); ?>">
                                <?php echo e(strtoupper(substr($admin->first_name, 0, 1))); ?>

                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">
                                    <?php echo e($admin->first_name); ?> <?php echo e($admin->last_name); ?>

                                    <?php if($admin->id === session('admin_id')): ?>
                                        <span class="text-xs text-[#1DA1F2] font-normal">(Vous)</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </td>

                    <!-- Email -->
                    <td class="px-6 py-4 text-gray-600"><?php echo e($admin->email); ?></td>

                    <!-- Téléphone -->
                    <td class="px-6 py-4 text-gray-600"><?php echo e($admin->phone ?? '—'); ?></td>

                    <!-- Rôle -->
                    <td class="px-6 py-4">
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full">
                            <?php echo e($admin->role->name ?? '—'); ?>

                        </span>
                    </td>

                    <!-- Statut -->
                    <td class="px-6 py-4">
                        <?php if($admin->status === 'active'): ?>
                            <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                                ✅ Actif
                            </span>
                        <?php else: ?>
                            <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">
                                🚫 Bloqué
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- Date -->
                    <td class="px-6 py-4 text-gray-500 text-xs">
                        <?php echo e($admin->created_at->format('d/m/Y')); ?>

                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4">
                        <div class="flex justify-center items-center gap-2">


                            <!-- Voir détails -->
                            <a href="<?php echo e(route('admin.profiles.show', $admin->id)); ?>"
                               class="bg-gray-100 text-gray-700 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-gray-200 transition">
                                👁 Voir
                            </a>

                            <!-- Modifier -->
                            <a href="<?php echo e(route('admin.profiles.edit', $admin->id)); ?>"
                               class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-xs font-semibold
                                      hover:bg-blue-200 transition">
                                ✏️ Modifier
                            </a>

                            <?php if($admin->id !== session('admin_id')): ?>
                                <!-- Bloquer / Activer -->
                                <?php if($admin->status === 'active'): ?>
                                    <form method="POST" action="<?php echo e(route('admin.profiles.block', $admin->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit"
                                                onclick="return confirm('Bloquer <?php echo e($admin->first_name); ?> ?')"
                                                class="bg-orange-100 text-orange-700 px-3 py-1 rounded-lg text-xs font-semibold
                                                       hover:bg-orange-200 transition">
                                            🚫 Bloquer
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo e(route('admin.profiles.activate', $admin->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit"
                                                class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs font-semibold
                                                       hover:bg-green-200 transition">
                                            ✅ Activer
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Supprimer -->
                                <form method="POST" action="<?php echo e(route('admin.profiles.destroy', $admin->id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                            onclick="return confirm('Supprimer définitivement <?php echo e($admin->first_name); ?> ?')"
                                            class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-xs font-semibold
                                                   hover:bg-red-200 transition">
                                        🗑 Supprimer
                                    </button>
                                </form>
                            <?php endif; ?>

                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                        Aucun administrateur trouvé.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/profiles/index.blade.php ENDPATH**/ ?>