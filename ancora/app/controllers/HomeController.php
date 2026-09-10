<?php
/**
 * Controller responsável pela renderização da Home / Landing Page
 */

class HomeController {
    public function index() {
        $pageTitle = "ÂNCORA — Gestão Escolar Moderna e Eficiente";
        require_once __DIR__ . '/../views/home/index.php';
    }
}
