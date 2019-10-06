<?php

function toHex(string $data): string {
    return strtoupper(bin2hex($data));
}

function view(string $view, $data = []) {
    extract($data);
    
    ob_start();
    require __DIR__."/../app/views/$view.tpl.php";
    $content = ob_get_contents();
    ob_end_clean();
    
    return $content;
}

function getConfig($name) {
    $appConfig = require getcwd().'/config/app.config.php';
    return $appConfig[$name];
}

function dd($data) {
    var_dump($data);
    die;
}

function redirect(string $url = '/') { 
    header('Location: '.$url);
    exit;
}

function isUserLoggedin(): bool {
    return $_SESSION['loggedin'] ?? FALSE;
}

function getUserLoggedFullName(): string {
    return $_SESSION['user']->name ?? '';
}

function getUserLoggedUsername(): string {
    return $_SESSION['user']->username ?? '';
}

function getUserLoggedEmail(): string {
    return $_SESSION['user']->email ?? '';
}

function getUserLoggedRole(): string {
    return $_SESSION['user']->roletype ?? '';
}

function getUserLoggedID(): string {
    return $_SESSION['user']->id ?? '';
}

function isUserAdmin() {
    return getUserLoggedRole() === 'admin';
}

function isUserEditor() {
    return getUserLoggedRole() === 'editor';
}

function isUser() {
    return getUserLoggedRole() === 'user';
}

function userCanCreate(){
    return isUserAdmin();
}

function userCanUpdate() {
    return isUserAdmin() || isUserEditor();
}

function userCanDelete(){
    return isUserAdmin();
}
