<?php return array (
  'App\\Providers\\EventServiceProvider' => 
  array (
    'Illuminate\\Auth\\Events\\Registered' => 
    array (
      0 => 'Illuminate\\Auth\\Listeners\\SendEmailVerificationNotification',
    ),
    'App\\Events\\PaymentCompleted' => 
    array (
      0 => 'App\\Listeners\\SendPaymentNotification',
      1 => 'App\\Listeners\\ProcessCompletedPayment',
    ),
    'App\\Events\\PaymentFailed' => 
    array (
      0 => 'App\\Listeners\\SendPaymentNotification',
    ),
    'App\\Events\\PayoutCompleted' => 
    array (
      0 => 'App\\Listeners\\SendPaymentNotification',
    ),
    'App\\Events\\RideCreated' => 
    array (
      0 => 'App\\Listeners\\NotifyDriversAboutNewRide',
    ),
  ),
  'Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider' => 
  array (
    'App\\Events\\RideCreated' => 
    array (
      0 => 'App\\Listeners\\NotifyDriversAboutNewRide@handle',
    ),
    'App\\Events\\PaymentCompleted' => 
    array (
      0 => 'App\\Listeners\\ProcessCompletedPayment@handle',
      1 => 'App\\Listeners\\SendPaymentNotification@handle',
    ),
    'App\\Events\\PaymentFailed' => 
    array (
      0 => 'App\\Listeners\\SendPaymentNotification@handle',
    ),
    'App\\Events\\PayoutCompleted' => 
    array (
      0 => 'App\\Listeners\\SendPaymentNotification@handle',
    ),
  ),
);