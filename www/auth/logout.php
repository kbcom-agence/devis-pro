<?php
require_once '../config/session.php';
require_once '../includes/functions.php';

// Détruire la session
session_destroy();

// Créer une nouvelle session pour le message flash
session_start();
setFlashMessage('success', 'Vous avez été déconnecté avec succès.');

redirect('/auth/login.php');
