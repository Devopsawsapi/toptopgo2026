

<?php $__env->startSection('content'); ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">🚗 Chauffeurs</h1>

    <a href="<?php echo e(route('admin.drivers.create')); ?>"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition">
        + Nouveau Chauffeur
    </a>
</div>

<div class="bg-white shadow rounded-lg p-6 overflow-x-auto">

<table class="w-full text-sm">
    <thead>
        <tr class="bg-gray-100 text-gray-700">
            <th class="p-3 text-left">Nom</th>
            <th class="p-3 text-left">Téléphone</th>
            <th class="p-3 text-left">Véhicule</th>
            <th class="p-3 text-left">Plaque</th>
            <th class="p-3 text-left">Couleur</th>
            <th class="p-3 text-left">Statut</th>
            <th class="p-3 text-left">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr class="border-b hover:bg-gray-50 transition">

            <td class="p-3 font-semibold">
                <?php echo e(optional($driver->user)->first_name); ?>

                <?php echo e(optional($driver->user)->last_name); ?>

            </td>

            <td class="p-3">
                <?php echo e(optional($driver->user)->phone); ?>

            </td>

            <td class="p-3">
                <?php echo e($driver->vehicle_brand); ?> <?php echo e($driver->vehicle_model); ?>

            </td>

            <td class="p-3 font-mono">
                <?php echo e($driver->vehicle_plate_number); ?>

            </td>

            <td class="p-3">
                <?php echo e($driver->vehicle_color); ?>

            </td>

            <td class="p-3">
                <?php if(optional($driver->user)->is_active): ?>
                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">
                        Actif
                    </span>
                <?php else: ?>
                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">
                        Désactivé
                    </span>
                <?php endif; ?>
            </td>

            <td class="p-3 flex gap-4 items-center">

                <a href="<?php echo e(route('admin.drivers.show', $driver)); ?>"
                   class="text-blue-600 hover:underline">
                   Détails
                </a>

                <form method="POST"
                      action="<?php echo e(route('admin.drivers.toggle-status', $driver)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>

                    <button type="submit"
                        class="<?php echo e(optional($driver->user)->is_active ? 'text-red-600 hover:underline' : 'text-green-600 hover:underline'); ?>">
                        <?php echo e(optional($driver->user)->is_active ? 'Désactiver' : 'Activer'); ?>

                    </button>
                </form>

            </td>

        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="7" class="p-6 text-center text-gray-500">
                Aucun chauffeur enregistré.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="mt-6">
    <?php echo e($drivers->links()); ?>

</div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/drivers/index.blade.php ENDPATH**/ ?>