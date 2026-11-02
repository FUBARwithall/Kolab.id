<?php

namespace App\Controllers;

/**
 * Contracts Controller
 * Full compatibility wrapper extending CampaignKolContracts
 */
class Contracts extends CampaignKolContracts
{
    // Inherits all endpoints: index, getDatatable, getOne, view, add, add_save,
    // generate, create_draft_ajax, edit, edit_save, delete, bulkDelete,
    // my_contracts, get_contract_html_ajax, save_contract_snk_ajax,
    // admin_counter_sign_action, kol_sign_action, sign_contract_action, signContract,
    // kol_issue_invoice_ajax, generate_initial_invoice_ajax, printContract,
    // print_pdf, download_pdf, single_pdf, export, export_pdf, file upload handlers
}
