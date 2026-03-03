

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

                <img src="<?php echo e(asset('storage/'.$driver->profile_photo)); ?>"
                     onclick="openImage(this.src)"
                     class="w-20 h-20 rounded-full object-cover border-4 border-[#1DA1F2] cursor-pointer hover:scale-110 transition"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                <div class="w-20 h-20 rounded-full bg-[#1DA1F2] items-center justify-center text-3xl font-bold text-white hidden">
                    <?php echo e(strtoupper(substr($driver->first_name, 0, 1))); ?>

                </div>

            <?php else: ?>

                <div class="w-20 h-20 rounded-full bg-[#1DA1F2] flex items-center justify-center text-3xl font-bold text-white">
                    <?php echo e(strtoupper(substr($driver->first_name, 0, 1))); ?>

                </div>

            <?php endif; ?>

            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <?php echo e($driver->first_name); ?> <?php echo e($driver->last_name); ?>

                </h2>

                <p class="text-gray-500"><?php echo e($driver->phone); ?></p>

                <div class="flex gap-2 mt-2">

                    <?php if($driver->status == 'approved'): ?>
                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                            ✅ Approuvé
                        </span>

                    <?php elseif($driver->status == 'pending'): ?>
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">
                            ⏳ En attente KYC
                        </span>

                    <?php elseif($driver->status == 'rejected'): ?>
                        <span class="bg-red-100 text-red-700 text-xs font-semibold px-3 py-1 rounded-full">
                            ❌ Rejeté
                        </span>

                    <?php else: ?>
                        <span class="bg-gray-100 text-gray-700 text-xs font-semibold px-3 py-1 rounded-full">
                            🚫 Suspendu
                        </span>
                    <?php endif; ?>


                    <?php if($driver->driver_status == 'online'): ?>

                        <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                            🟢 En ligne
                        </span>

                    <?php else: ?>

                        <span class="bg-gray-100 text-gray-500 text-xs font-semibold px-3 py-1 rounded-full">
                            ⚫ Hors ligne
                        </span>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">
                    Date de naissance
                </p>

                <p class="font-semibold text-gray-800">
                    <?php echo e($driver->birth_date ? \Carbon\Carbon::parse($driver->birth_date)->format('d/m/Y') : '—'); ?>

                </p>
            </div>


            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">
                    Lieu de naissance
                </p>

                <p class="font-semibold text-gray-800">
                    <?php echo e($driver->birth_place ?? '—'); ?>

                </p>
            </div>


            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">
                    Pays de naissance
                </p>

                <p class="font-semibold text-gray-800">
                    <?php echo e($driver->country_birth ?? '—'); ?>

                </p>
            </div>


            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">
                    Inscrit le
                </p>

                <p class="font-semibold text-gray-800">
                    <?php echo e($driver->created_at->format('d/m/Y à H:i')); ?>

                </p>
            </div>


            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">
                    Ville
                </p>

                <p class="font-semibold text-gray-800">
                    <?php echo e($driver->vehicle_city ?? '—'); ?>

                </p>
            </div>


            <div class="bg-gray-50 p-4 rounded-xl">
                <p class="text-xs text-gray-400 uppercase mb-1">
                    Pays
                </p>

                <p class="font-semibold text-gray-800">
                    <?php echo e($driver->vehicle_country ?? '—'); ?>

                </p>
            </div>

        </div>

    </div>



    <!-- DOCUMENTS KYC -->

    <div class="bg-white rounded-2xl shadow-md p-8 mb-6">

        <h2 class="text-lg font-bold text-gray-700 mb-6 pb-3 border-b border-gray-100">
            📄 Documents KYC
        </h2>


        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">


            <?php

            $docs = [

                ['label' => '🪪 CNI Recto','field' => 'id_card_front'],
                ['label' => '🪪 CNI Verso','field' => 'id_card_back'],
                ['label' => '📋 Permis Recto','field' => 'license_front'],
                ['label' => '📋 Permis Verso','field' => 'license_back'],
                ['label' => '🚗 Carte grise','field' => 'vehicle_registration'],
                ['label' => '🛡 Assurance','field' => 'insurance'],

            ];

            ?>



            <?php $__currentLoopData = $docs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <div class="border border-gray-200 rounded-xl overflow-hidden">

                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <p class="text-sm font-semibold text-gray-700">
                        <?php echo e($doc['label']); ?>

                    </p>
                </div>


                <div class="p-3">

                    <?php if($driver->{$doc['field']}): ?>

                        <?php

                        $fileUrl = asset('storage/'.$driver->{$doc['field']});

                        $ext = strtolower(pathinfo(parse_url($fileUrl, PHP_URL_PATH), PATHINFO_EXTENSION));

                        ?>


                        <?php if(in_array($ext,['jpg','jpeg','png','webp'])): ?>

                        <a href="<?php echo e($fileUrl); ?>" target="_blank">

                            <img src="<?php echo e($fileUrl); ?>"
                                 class="w-full h-32 object-cover rounded-lg hover:opacity-90 transition cursor-pointer">

                        </a>


                        <?php else: ?>

                        <a href="<?php echo e($fileUrl); ?>" target="_blank"
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


</div>


<!-- ZOOM PHOTO PROFIL -->

<div id="imageModal"
     class="fixed inset-0 bg-black bg-opacity-80 hidden
            flex items-center justify-center z-50">

    <img id="modalImage"
         class="max-h-[90vh] max-w-[90vw] rounded-xl shadow-lg">

</div>


<script>

function openImage(src){

    document.getElementById('modalImage').src = src;

    document.getElementById('imageModal').classList.remove('hidden');

}

document.getElementById('imageModal').onclick = function(){

    this.classList.add('hidden');

}

</script>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views\admin\drivers\show.blade.php ENDPATH**/ ?>