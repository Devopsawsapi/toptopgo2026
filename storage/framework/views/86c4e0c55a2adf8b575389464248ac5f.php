

<?php $__env->startSection('content'); ?>
<div class="p-6 max-w-4xl mx-auto">

    
    <div class="flex items-center gap-4 mb-6">
        <a href="<?php echo e(route('admin.sos.index')); ?>"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
            ← Retour
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🆘 Détail Alerte SOS #<?php echo e($alert->id); ?></h1>
            <p class="text-sm text-gray-400 mt-0.5"><?php echo e($alert->created_at->format('d/m/Y à H:i:s')); ?></p>
        </div>
        <span class="ml-auto text-sm px-3 py-1.5 rounded-full font-medium
            <?php echo e($alert->status === 'active' ? 'bg-red-500 text-white animate-pulse' : 'bg-green-100 text-green-700'); ?>">
            <?php echo e($alert->status === 'active' ? '🆘 ACTIVE' : '✅ Traitée'); ?>

        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-4">
                <?php echo e(str_contains($alert->sender_type, 'Driver') ? '🚗 Chauffeur' : '👤 Utilisateur'); ?>

            </h3>
            <?php if($alert->sender): ?>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Nom</span>
                        <span class="font-medium"><?php echo e($alert->sender->first_name); ?> <?php echo e($alert->sender->last_name); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Téléphone</span>
                        <span class="font-medium"><?php echo e($alert->sender->phone ?? '—'); ?></span>
                    </div>
                    <?php if(str_contains($alert->sender_type, 'Driver')): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Véhicule</span>
                            <span class="font-medium">
                                <?php echo e($alert->sender->vehicle_brand); ?> <?php echo e($alert->sender->vehicle_model); ?>

                                — <?php echo e($alert->sender->vehicle_plate); ?>

                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Statut</span>
                            <span class="font-medium"><?php echo e($alert->sender->driver_status); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-400 text-sm">Expéditeur introuvable</p>
            <?php endif; ?>
        </div>

        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-700 mb-4">📋 Détails de l'alerte</h3>
            <div class="space-y-2 text-sm">
                <?php if($alert->message): ?>
                    <div>
                        <span class="text-gray-500 block mb-1">Message :</span>
                        <p class="bg-red-50 text-red-800 px-3 py-2 rounded-lg"><?php echo e($alert->message); ?></p>
                    </div>
                <?php endif; ?>
                <?php if($alert->lat && $alert->lng): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Position GPS</span>
                        <span class="font-medium font-mono text-xs"><?php echo e($alert->lat); ?>, <?php echo e($alert->lng); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($alert->trip): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Course liée</span>
                        <span class="font-medium">#<?php echo e($alert->trip_id); ?> — <?php echo e($alert->trip->status); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($alert->status === 'treated'): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Traité par</span>
                        <span class="font-medium"><?php echo e($alert->treatedBy->name ?? '—'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Traité le</span>
                        <span class="font-medium"><?php echo e($alert->treated_at?->format('d/m/Y H:i')); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($alert->lat && $alert->lng): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden md:col-span-2">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-700">📍 Position de l'alerte</h3>
            </div>
            <div id="alertMap" style="height:300px; z-index:1;"></div>
        </div>
        <?php endif; ?>

        
        <div class="md:col-span-2 flex gap-3 justify-end">
            <?php if($alert->status === 'active'): ?>
                <form method="POST" action="<?php echo e(route('admin.sos.treat', $alert->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">
                        ✓ Marquer comme traitée
                    </button>
                </form>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('admin.sos.destroy', $alert->id)); ?>"
                  onsubmit="return confirm('Supprimer cette alerte définitivement ?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="bg-red-100 hover:bg-red-200 text-red-700 px-6 py-2.5 rounded-lg text-sm font-medium transition">
                    🗑 Supprimer
                </button>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php if($alert->lat && $alert->lng): ?>
<script>
const alertMap = L.map('alertMap').setView([<?php echo e($alert->lat); ?>, <?php echo e($alert->lng); ?>], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
}).addTo(alertMap);

const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="40" height="50" viewBox="0 0 40 50">
    <path d="M20 0 C9 0 0 9 0 20 C0 33 20 50 20 50 C20 50 40 33 40 20 C40 9 31 0 20 0Z"
          fill="#ef4444" stroke="white" stroke-width="2"/>
    <text x="20" y="26" text-anchor="middle" font-size="18" fill="white">🆘</text>
</svg>`;

const icon = L.divIcon({ html: svg, iconSize:[40,50], iconAnchor:[20,50], popupAnchor:[0,-50], className:'' });
L.marker([<?php echo e($alert->lat); ?>, <?php echo e($alert->lng); ?>], { icon })
 .addTo(alertMap)
 .bindPopup('<b>Position de l\'alerte SOS</b>')
 .openPopup();
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views\admin\sos\show.blade.php ENDPATH**/ ?>