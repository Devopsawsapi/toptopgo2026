

<?php $__env->startSection('content'); ?>

<!-- HEADER -->
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            🚗 Gestion des <span class="text-[#1DA1F2]">Chauffeurs</span>
        </h1>
        <p class="text-gray-500 text-sm mt-1">Liste et gestion de tous les chauffeurs</p>
    </div>
    <a href="<?php echo e(route('admin.drivers.create')); ?>"
       class="bg-[#1DA1F2] text-white px-6 py-3 rounded-xl font-semibold
              hover:bg-[#FFC107] hover:text-black transition-all duration-300
              hover:-translate-y-1 hover:shadow-lg flex items-center gap-2">
        ➕ Nouveau Chauffeur
    </a>
</div>

<!-- STATS -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-5 rounded-2xl shadow-md border-l-4 border-blue-500">
        <p class="text-gray-500 text-sm">Total</p>
        <h2 class="text-3xl font-bold text-blue-500 mt-1"><?php echo e($drivers->total()); ?></h2>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-md border-l-4 border-green-500">
        <p class="text-gray-500 text-sm">Approuvés</p>
        <h2 class="text-3xl font-bold text-green-500 mt-1"><?php echo e(\App\Models\Driver\Driver::where('status','approved')->count()); ?></h2>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-md border-l-4 border-yellow-500">
        <p class="text-gray-500 text-sm">En attente</p>
        <h2 class="text-3xl font-bold text-yellow-500 mt-1"><?php echo e(\App\Models\Driver\Driver::where('status','pending')->count()); ?></h2>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-md border-l-4 border-red-500">
        <p class="text-gray-500 text-sm">Suspendus</p>
        <h2 class="text-3xl font-bold text-red-500 mt-1"><?php echo e(\App\Models\Driver\Driver::where('status','suspended')->count()); ?></h2>
    </div>
</div>

<!-- FILTRES -->
<div class="bg-white p-6 rounded-2xl shadow-md mb-6">
    <form method="GET" action="<?php echo e(route('admin.drivers.index')); ?>" class="flex flex-wrap gap-4">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>"
               placeholder="Nom, téléphone..."
               class="px-4 py-2 border rounded-xl focus:ring-2 focus:ring-[#1DA1F2] outline-none flex-1">
        <select name="status" class="px-4 py-2 border rounded-xl focus:ring-2 focus:ring-[#1DA1F2] outline-none bg-white">
            <option value="">Tous les statuts</option>
            <option value="pending"   <?php echo e(request('status') == 'pending'   ? 'selected' : ''); ?>>⏳ En attente</option>
            <option value="approved"  <?php echo e(request('status') == 'approved'  ? 'selected' : ''); ?>>✅ Approuvés</option>
            <option value="rejected"  <?php echo e(request('status') == 'rejected'  ? 'selected' : ''); ?>>❌ Rejetés</option>
            <option value="suspended" <?php echo e(request('status') == 'suspended' ? 'selected' : ''); ?>>🚫 Suspendus</option>
        </select>
        <button type="submit" class="bg-[#1DA1F2] text-white px-6 py-2 rounded-xl hover:bg-[#FFC107] hover:text-black transition">
            Filtrer
        </button>
        <a href="<?php echo e(route('admin.drivers.index')); ?>" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-xl hover:bg-gray-300 transition">
            Reset
        </a>
    </form>
</div>

<!-- TABLEAU -->
<div class="bg-white rounded-2xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4 text-left">Chauffeur</th>
                    <th class="px-6 py-4 text-left">Téléphone</th>
                    <th class="px-6 py-4 text-left">Véhicule</th>
                    <th class="px-6 py-4 text-left">Type</th>
                    <th class="px-6 py-4 text-left">Statut KYC</th>
                    <th class="px-6 py-4 text-left">En ligne</th>
                    <th class="px-6 py-4 text-left">Inscrit le</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50 transition">

                    <!-- Nom -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <?php if($driver->profile_photo): ?>
                                
                                <img src="<?php echo e($driver->profile_photo); ?>"
                                     class="w-9 h-9 rounded-full object-cover border"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-9 h-9 rounded-full bg-[#1DA1F2] items-center justify-center text-white font-bold text-sm hidden">
                                    <?php echo e(strtoupper(substr($driver->first_name, 0, 1))); ?>

                                </div>
                            <?php else: ?>
                                <div class="w-9 h-9 rounded-full bg-[#1DA1F2] flex items-center justify-center text-white font-bold text-sm">
                                    <?php echo e(strtoupper(substr($driver->first_name, 0, 1))); ?>

                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="font-semibold text-gray-800"><?php echo e($driver->first_name); ?> <?php echo e($driver->last_name); ?></p>
                                <p class="text-xs text-gray-400"><?php echo e($driver->vehicle_city ?? '—'); ?></p>
                            </div>
                        </div>
                    </td>

                    <!-- Téléphone -->
                    <td class="px-6 py-4 text-gray-600"><?php echo e($driver->phone); ?></td>

                    <!-- Véhicule -->
                    <td class="px-6 py-4 text-gray-600">
                        <?php echo e($driver->vehicle_brand ?? '—'); ?> <?php echo e($driver->vehicle_model ?? ''); ?><br>
                        <span class="text-xs text-gray-400"><?php echo e($driver->vehicle_plate ?? '—'); ?></span>
                    </td>

                    <!-- Type -->
                    <td class="px-6 py-4">
                        <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">
                            <?php echo e($driver->vehicle_type ?? '—'); ?>

                        </span>
                    </td>

                    <!-- Statut KYC -->
                    <td class="px-6 py-4">
                        <?php if($driver->status == 'approved'): ?>
                            <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">✅ Approuvé</span>
                        <?php elseif($driver->status == 'pending'): ?>
                            <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">⏳ En attente</span>
                        <?php elseif($driver->status == 'rejected'): ?>
                            <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">❌ Rejeté</span>
                        <?php else: ?>
                            <span class="bg-gray-100 text-gray-700 text-xs font-semibold px-3 py-1 rounded-full">🚫 Suspendu</span>
                        <?php endif; ?>
                    </td>

                    <!-- Driver status -->
                    <td class="px-6 py-4">
                        <?php if($driver->driver_status == 'online'): ?>
                            <span class="flex items-center gap-1 text-green-600 text-xs font-semibold">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span> En ligne
                            </span>
                        <?php elseif($driver->driver_status == 'pause'): ?>
                            <span class="flex items-center gap-1 text-yellow-600 text-xs font-semibold">
                                <span class="w-2 h-2 bg-yellow-500 rounded-full"></span> Pause
                            </span>
                        <?php else: ?>
                            <span class="flex items-center gap-1 text-gray-400 text-xs font-semibold">
                                <span class="w-2 h-2 bg-gray-400 rounded-full"></span> Hors ligne
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- Date -->
                    <td class="px-6 py-4 text-gray-500 text-xs">
                        <?php echo e($driver->created_at->format('d/m/Y')); ?>

                    </td>

                    <!-- Actions -->
                    <td class="px-6 py-4">
                        <div class="flex justify-center items-center gap-2 flex-wrap">

                            <a href="<?php echo e(route('admin.drivers.show', $driver->id)); ?>"
                               class="bg-gray-100 text-gray-700 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-gray-200 transition">
                                👁 Voir
                            </a>

                            <a href="<?php echo e(route('admin.drivers.edit', $driver->id)); ?>"
                               class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-blue-200 transition">
                                ✏️ Modifier
                            </a>

                            <?php if($driver->status == 'pending'): ?>
                                <form method="POST" action="<?php echo e(route('admin.drivers.approve', $driver->id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-green-200 transition">
                                        ✅ Approuver
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo e(route('admin.drivers.reject', $driver->id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" onclick="return confirm('Rejeter ce chauffeur ?')"
                                            class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-red-200 transition">
                                        ❌ Rejeter
                                    </button>
                                </form>
                            <?php elseif($driver->status == 'approved'): ?>
                                <form method="POST" action="<?php echo e(route('admin.drivers.suspend', $driver->id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" onclick="return confirm('Suspendre ce chauffeur ?')"
                                            class="bg-orange-100 text-orange-700 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-orange-200 transition">
                                        🚫 Suspendre
                                    </button>
                                </form>
                            <?php elseif($driver->status == 'suspended'): ?>
                                <form method="POST" action="<?php echo e(route('admin.drivers.activate', $driver->id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                            class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-green-200 transition">
                                        ✅ Réactiver
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" action="<?php echo e(route('admin.drivers.destroy', $driver->id)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" onclick="return confirm('Supprimer définitivement ce chauffeur ?')"
                                        class="bg-red-100 text-red-700 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-red-200 transition">
                                    🗑
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                        Aucun chauffeur trouvé.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <?php if($drivers->hasPages()): ?>
    <div class="px-6 py-4 border-t border-gray-100">
        <?php echo e($drivers->appends(request()->query())->links()); ?>

    </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views\admin\drivers\index.blade.php ENDPATH**/ ?>