

<?php $__env->startSection('content'); ?>

<div class="max-w-5xl mx-auto">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('admin.drivers.index')); ?>" class="text-gray-400 hover:text-gray-700 transition text-2xl">←</a>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">👤 Profil Chauffeur</h1>
                <p class="text-gray-500 text-sm mt-1"><?php echo e($driver->first_name); ?> <?php echo e($driver->last_name); ?></p>
            </div>
        </div>
        <a href="<?php echo e(route('admin.drivers.edit', $driver->id)); ?>"
           class="bg-[#1DA1F2] text-white px-6 py-3 rounded-xl font-semibold hover:bg-[#FFC107] hover:text-black transition-all duration-300">
            ✏️ Modifier
        </a>
    </div>

    <!-- INFO PERSO -->
    <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
        <div class="flex items-center gap-6 mb-6">
            <?php if($driver->profile_photo): ?>
                <img src="<?php echo e(asset('storage/' . $driver->profile_photo)); ?>"
                     class="w-20 h-20 rounded-full object-cover border-4 border-[#1DA1F2]">
            <?php else: ?>
                <div class="w-20 h-20 rounded-full bg-[#1DA1F2] flex items-center justify-center text-3xl font-bold text-white">
                    <?php echo e(strtoupper(substr($driver->first_name, 0, 1))); ?>

                </div>
            <?php endif; ?>
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?php echo e($driver->first_name); ?> <?php echo e($driver->last_name); ?></h2>
                <p class="text-gray-500"><?php echo e($driver->phone); ?></p>
                <div class="flex gap-2 mt-2">
                    <?php if($driver->status == 'approved'): ?>
                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">✅ Approuvé</span>
                    <?php elseif($driver->status == 'pending'): ?>
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">⏳ En attente KYC</span>
                    <?php elseif($driver->status == 'rejected'): ?>
                        <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">❌ Rejeté</span>
                    <?php else: ?>
                        <span class="bg-gray-100 text-gray-700 text-xs font-semibold px-3 py-1 rounded-full">🚫 Suspendu</span>
                    <?php endif; ?>
                    <?php if($driver->driver_status == 'online'): ?>
                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">🟢 En ligne</span>
                    <?php else: ?>
                        <span class="bg-gray-100 text-gray-500 text-xs font-semibold px-3 py-1 rounded-full">⚫ Hors ligne</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Date de naissance</p>
                <p class="font-semibold text-gray-800"><?php echo e($driver->birth_date ? \Carbon\Carbon::parse($driver->birth_date)->format('d/m/Y') : '—'); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Lieu de naissance</p>
                <p class="font-semibold text-gray-800"><?php echo e($driver->birth_place ?? '—'); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Pays de naissance</p>
                <p class="font-semibold text-gray-800"><?php echo e($driver->country_birth ?? '—'); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Inscrit le</p>
                <p class="font-semibold text-gray-800"><?php echo e($driver->created_at->format('d/m/Y à H:i')); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Ville</p>
                <p class="font-semibold text-gray-800"><?php echo e($driver->vehicle_city ?? '—'); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Pays</p>
                <p class="font-semibold text-gray-800"><?php echo e($driver->vehicle_country ?? '—'); ?></p>
            </div>
        </div>
    </div>

    <!-- VÉHICULE -->
    <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
        <h2 class="text-lg font-bold text-gray-700 mb-4 pb-3 border-b border-gray-100">🚗 Véhicule</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Plaque</p>
                <p class="font-semibold text-gray-800"><?php echo e($driver->vehicle_plate ?? '—'); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Marque / Modèle</p>
                <p class="font-semibold text-gray-800"><?php echo e($driver->vehicle_brand ?? '—'); ?> <?php echo e($driver->vehicle_model ?? ''); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Type</p>
                <p class="font-semibold text-gray-800"><?php echo e($driver->vehicle_type ?? '—'); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">Couleur</p>
                <p class="font-semibold text-gray-800"><?php echo e($driver->vehicle_color ?? '—'); ?></p>
            </div>
        </div>
    </div>

    <!-- DOCUMENTS -->
    <div class="bg-white rounded-2xl shadow-md p-8 mb-6">
        <h2 class="text-lg font-bold text-gray-700 mb-6 pb-3 border-b border-gray-100">📄 Documents KYC</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <?php
            $docs = [
                ['label' => '🪪 CNI Recto',      'field' => 'id_card_front'],
                ['label' => '🪪 CNI Verso',       'field' => 'id_card_back'],
                ['label' => '📋 Permis Recto',    'field' => 'license_front'],
                ['label' => '📋 Permis Verso',    'field' => 'license_back'],
                ['label' => '🚗 Carte grise',     'field' => 'vehicle_registration'],
                ['label' => '🛡 Assurance',       'field' => 'insurance'],
            ];
            ?>

            <?php $__currentLoopData = $docs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <p class="text-sm font-semibold text-gray-700"><?php echo e($doc['label']); ?></p>
                </div>
                <div class="p-3">
                    <?php if($driver->{$doc['field']}): ?>
                        <?php $ext = pathinfo($driver->{$doc['field']}, PATHINFO_EXTENSION); ?>
                        <?php if(in_array(strtolower($ext), ['jpg','jpeg','png','webp'])): ?>
                            <a href="<?php echo e(asset('storage/' . $driver->{$doc['field']})); ?>" target="_blank">
                                <img src="<?php echo e(asset('storage/' . $driver->{$doc['field']})); ?>"
                                     class="w-full h-32 object-cover rounded-lg hover:opacity-90 transition cursor-pointer">
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(asset('storage/' . $driver->{$doc['field']})); ?>" target="_blank"
                               class="flex items-center gap-2 text-[#1DA1F2] hover:underline text-sm">
                                📎 Voir le fichier
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="h-32 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-sm">
                            Non fourni
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>
    </div>

    <!-- ACTIONS -->
    <div class="flex gap-4 mb-10 flex-wrap">
        <?php if($driver->status == 'pending'): ?>
            <form method="POST" action="<?php echo e(route('admin.drivers.approve', $driver->id)); ?>" class="flex-1">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full bg-green-500 text-white py-3 rounded-xl font-semibold hover:bg-green-600 transition">
                    ✅ Approuver le chauffeur
                </button>
            </form>
            <form method="POST" action="<?php echo e(route('admin.drivers.reject', $driver->id)); ?>" class="flex-1">
                <?php echo csrf_field(); ?>
                <button type="submit" onclick="return confirm('Rejeter ce chauffeur ?')"
                        class="w-full bg-red-100 text-red-700 py-3 rounded-xl font-semibold hover:bg-red-200 transition">
                    ❌ Rejeter
                </button>
            </form>
        <?php elseif($driver->status == 'approved'): ?>
            <form method="POST" action="<?php echo e(route('admin.drivers.suspend', $driver->id)); ?>" class="flex-1">
                <?php echo csrf_field(); ?>
                <button type="submit" onclick="return confirm('Suspendre ce chauffeur ?')"
                        class="w-full bg-orange-100 text-orange-700 py-3 rounded-xl font-semibold hover:bg-orange-200 transition">
                    🚫 Suspendre
                </button>
            </form>
        <?php elseif($driver->status == 'suspended'): ?>
            <form method="POST" action="<?php echo e(route('admin.drivers.activate', $driver->id)); ?>" class="flex-1">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full bg-green-100 text-green-700 py-3 rounded-xl font-semibold hover:bg-green-200 transition">
                    ✅ Réactiver
                </button>
            </form>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.drivers.destroy', $driver->id)); ?>" class="flex-1">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <button type="submit" onclick="return confirm('Supprimer définitivement ce chauffeur ?')"
                    class="w-full bg-red-100 text-red-700 py-3 rounded-xl font-semibold hover:bg-red-200 transition">
                🗑 Supprimer
            </button>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/drivers/show.blade.php ENDPATH**/ ?>