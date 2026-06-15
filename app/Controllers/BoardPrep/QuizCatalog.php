<?php



namespace App\Controllers\BoardPrep;



class QuizCatalog extends BoardPrepBaseController

{

    public function index()

    {

        if ($redirect = $this->requireAuth()) {

            return $redirect;

        }



        return redirect()->to(board_prep_url('dashboard'));

    }

}
