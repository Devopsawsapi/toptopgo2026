

<?php $__env->startSection('content'); ?>
<div class="p-6">

    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">💬 Conversations Users ↔ Chauffeurs</h1>
            <p class="text-sm text-gray-500 mt-1">Toutes les conversations entre utilisateurs et chauffeurs</p>
        </div>
    </div>

    
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-gray-500 mb-1">Total Messages</div>
            <div class="text-3xl font-bold text-blue-600"><?php echo e($totalMessages); ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-gray-500 mb-1">Non lus</div>
            <div class="text-3xl font-bold text-orange-500"><?php echo e($unreadMessages); ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="text-sm text-gray-500 mb-1">Conversations actives</div>
            <div class="text-3xl font-bold text-green-600"><?php echo e($totalTripsWithMessages); ?></div>
        </div>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <form method="GET" action="<?php echo e(route('admin.messages.index')); ?>" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    👤 Filtrer par Utilisateur
                </label>
                <select name="user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tous les utilisateurs --</option>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($u->id); ?>" <?php echo e(request('user_id') == $u->id ? 'selected' : ''); ?>>
                            <?php echo e($u->first_name); ?> <?php echo e($u->last_name); ?>

                            <?php if($u->phone): ?> (<?php echo e($u->phone); ?>) <?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    🚗 Filtrer par Chauffeur
                </label>
                <select name="driver_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tous les chauffeurs --</option>
                    <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($d->id); ?>" <?php echo e(request('driver_id') == $d->id ? 'selected' : ''); ?>>
                            <?php echo e($d->first_name); ?> <?php echo e($d->last_name); ?>

                            <?php if($d->phone): ?> (<?php echo e($d->phone); ?>) <?php endif; ?>
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                    🔍 Filtrer
                </button>
                <a href="<?php echo e(route('admin.messages.index')); ?>"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium transition">
                    ✕ Reset
                </a>
            </div>
        </form>
    </div>

    
    <div class="flex gap-4" style="height: 65vh;">

        
        <div class="w-1/3 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-700 text-sm">
                    📋 Conversations
                    <span class="ml-2 bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">
                        <?php echo e($trips->total()); ?>

                    </span>
                </h2>
            </div>

            <div class="overflow-y-auto flex-1">
                <?php $__empty_1 = true; $__currentLoopData = $trips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $isActive = isset($trip) && $trip->id === $t->id;
                        $lastMsg  = $t->messages->first();
                        $params   = array_filter(['user_id' => request('user_id'), 'driver_id' => request('driver_id')]);
                    ?>

                    <a href="<?php echo e(route('admin.messages.show', array_merge(['trip' => $t->id], $params))); ?>"
                        class="block p-4 border-b border-gray-50 hover:bg-blue-50 transition
                               <?php echo e($isActive ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''); ?>">

                        
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                <?php echo e(strtoupper(substr($t->user->first_name ?? 'U', 0, 1))); ?>

                            </div>
                            <span class="text-xs text-gray-400">↔</span>
                            <div class="w-7 h-7 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold">
                                <?php echo e(strtoupper(substr($t->driver->first_name ?? 'D', 0, 1))); ?>

                            </div>
                        </div>

                        
                        <div class="text-sm font-semibold text-gray-800">
                            <span class="text-indigo-600"><?php echo e($t->user->first_name ?? 'User supprimé'); ?></span>
                            <span class="text-gray-400 mx-1">↔</span>
                            <span class="text-green-600"><?php echo e($t->driver->first_name ?? 'Driver supprimé'); ?></span>
                        </div>

                        
                        <div class="text-xs text-gray-400 mt-0.5">Trip #<?php echo e($t->id); ?></div>
                        <?php if($lastMsg): ?>
                            <div class="text-xs text-gray-500 mt-1 truncate">
                                <?php echo e(Str::limit($lastMsg->content, 45)); ?>

                            </div>
                            <div class="text-xs text-gray-300 mt-0.5">
                                <?php echo e($lastMsg->created_at->diffForHumans()); ?>

                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center text-gray-400">
                        <div class="text-4xl mb-2">💬</div>
                        <p class="text-sm">Aucune conversation trouvée</p>
                    </div>
                <?php endif; ?>
            </div>

            
            <?php if($trips->hasPages()): ?>
                <div class="p-3 border-t border-gray-100 text-center">
                    <?php echo e($trips->appends(request()->query())->links('pagination::simple-tailwind')); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">

            <?php if(isset($trip) && isset($messages)): ?>

                
                <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-2">
                            <div class="w-9 h-9 rounded-full bg-indigo-200 text-indigo-800 flex items-center justify-center text-sm font-bold ring-2 ring-white">
                                <?php echo e(strtoupper(substr($trip->user->first_name ?? 'U', 0, 1))); ?>

                            </div>
                            <div class="w-9 h-9 rounded-full bg-green-200 text-green-800 flex items-center justify-center text-sm font-bold ring-2 ring-white">
                                <?php echo e(strtoupper(substr($trip->driver->first_name ?? 'D', 0, 1))); ?>

                            </div>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">
                                <span class="text-indigo-600"><?php echo e($trip->user->first_name ?? 'User supprimé'); ?> <?php echo e($trip->user->last_name ?? ''); ?></span>
                                <span class="text-gray-400 mx-1">↔</span>
                                <span class="text-green-600"><?php echo e($trip->driver->first_name ?? 'Driver supprimé'); ?> <?php echo e($trip->driver->last_name ?? ''); ?></span>
                            </div>
                            <div class="text-xs text-gray-400">
                                Trip #<?php echo e($trip->id); ?> •
                                <?php echo e($messages->count()); ?> message(s)
                            </div>
                        </div>
                    </div>

                    
                    <div class="flex gap-3 text-xs">
                        <?php if(isset($trip->user) && $trip->user->phone): ?>
                            <span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded">
                                👤 <?php echo e($trip->user->phone); ?>

                            </span>
                        <?php endif; ?>
                        <?php if(isset($trip->driver) && $trip->driver->phone): ?>
                            <span class="bg-green-50 text-green-700 px-2 py-1 rounded">
                                🚗 <?php echo e($trip->driver->phone); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-gray-50" id="messagesBox">
                    <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isUser = str_contains($message->sender_type, 'User');
                        ?>

                        <div class="flex <?php echo e($isUser ? 'justify-start' : 'justify-end'); ?> items-end gap-2">

                            <?php if($isUser): ?>
                                <div class="w-8 h-8 rounded-full bg-indigo-200 text-indigo-800 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    <?php echo e(strtoupper(substr($trip->user->first_name ?? 'U', 0, 1))); ?>

                                </div>
                            <?php endif; ?>

                            <div class="max-w-xs lg:max-w-md">
                                <div class="text-xs text-gray-400 mb-1 <?php echo e($isUser ? 'text-left' : 'text-right'); ?>">
                                    <?php if($isUser): ?>
                                        👤 <?php echo e($trip->user->first_name ?? 'User'); ?>

                                    <?php else: ?>
                                        🚗 <?php echo e($trip->driver->first_name ?? 'Driver'); ?>

                                    <?php endif; ?>
                                </div>

                                <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed
                                    <?php echo e($isUser
                                        ? 'bg-white border border-gray-200 text-gray-800 rounded-tl-none shadow-sm'
                                        : 'bg-blue-600 text-white rounded-tr-none shadow-sm'); ?>">
                                    <?php echo e($message->content); ?>

                                </div>

                                <div class="text-xs text-gray-400 mt-1 <?php echo e($isUser ? 'text-left' : 'text-right'); ?>">
                                    <?php echo e($message->created_at->format('H:i')); ?>

                                    <?php if(!$isUser): ?>
                                        <?php if($message->is_read): ?>
                                            <span class="text-blue-400 ml-1">✓✓</span>
                                        <?php else: ?>
                                            <span class="text-gray-300 ml-1">✓</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if(!$isUser): ?>
                                <div class="w-8 h-8 rounded-full bg-green-200 text-green-800 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    <?php echo e(strtoupper(substr($trip->driver->first_name ?? 'D', 0, 1))); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center text-gray-400 py-10">
                            <div class="text-4xl mb-2">📭</div>
                            <p>Aucun message dans cette conversation</p>
                        </div>
                    <?php endif; ?>
                </div>

                
                <div class="p-3 border-t border-gray-100 bg-gray-50 text-center">
                    <span class="text-xs text-gray-400 italic">
                        🔒 Vue en lecture seule — Interface d'administration
                    </span>
                </div>

            <?php else: ?>
                
                <div class="flex-1 flex items-center justify-center text-gray-400">
                    <div class="text-center">
                        <div class="text-6xl mb-4">💬</div>
                        <p class="text-lg font-medium text-gray-500">Sélectionnez une conversation</p>
                        <p class="text-sm mt-1">Cliquez sur une conversation dans la liste pour voir les messages</p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const box = document.getElementById('messagesBox');
    if (box) {
        box.scrollTop = box.scrollHeight;
    }

    <?php if(isset($trip)): ?>
    setInterval(function () {
        location.reload();
    }, 10000);
    <?php endif; ?>
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\SMART\Desktop\Nouveau dossier\Backendtoptopgo\Backendtoptopgo\resources\views/admin/messages/user-driver.blade.php ENDPATH**/ ?>