<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovisionnementRequest;
use App\Produit;
use App\Fournisseur;
use App\Approvisionnement;
use App\Http\Controllers\Admin\BaseController;
use DateTime;
use Carbon\Carbon;


class ApprovisionnementController extends BaseController
{

    public function __construct() {
        parent::__construct();
        $this->middleware('approvision_control');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $produits = Produit::all();
        return view('stock.product.historic_approvision')->with(compact('produits'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $produits = Produit::all();
        $fournisseurs = Fournisseur::all();

        return view('stock.product.approvision')->with(compact('produits','fournisseurs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ApprovisionnementRequest $request)
    {
        $request->validated();
        $data = $request->all();
        //On procede au traitement si et seulement le produit concerné par l'approvisionnement existe
        if($produit = Produit::findOrFail($data['produit'])){

            //On sauvegarde les details de l'approvisionnement
            $approvision = new Approvisionnement;
            $approvision->num_BL = $data['num_bl'];
            $approvision->produit = $data['produit'];
            $approvision->fournisseur = $data['fournisseur'];
            $approvision->conditionnement = $data['conditionnement'];
            $approvision->qte_cond = $data['qte_par_cond'];
            $approvision->total = $data['total'];
            $date_appr = DateTime::createFromFormat('d/m/Y', $data['date_approvision']);
            $approvision->date_appr = $date_appr->format('Y-m-d');
            $date_peremption = DateTime::createFromFormat('d/m/Y', $data['date_peremption']);
            $approvision->date_peremption = $date_peremption->format('Y-m-d');
            $approvision->save();
            //On augmente le stock actuel
            $produit->qte_stock+=$data['total'];
            $produit->save();
            $successMessage = 'Le stock a été approvisionné';
            return redirect('/stock/approvisionner')->with('successMessage', $successMessage);
        }

        die;

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function liste_appro_date(Request $request){

        $data = $request->all();
        $date_debut = DateTime::createFromFormat('d/m/Y', $data['date_debut']);
        $date_fin = DateTime::createFromFormat('d/m/Y', $data['date_fin']);
        $date_debut = $date_debut->format('Y-m-d');
        $date_fin = $date_fin->format('Y-m-d');
        $produit = $data['produit'];
        $response = '';
        //On verifie s'il existe des approvisionnements a la date choisis par l'utilisateur
        if($approvisionnements = Approvisionnement::whereDate('date_appr', '>=', $date_debut)->whereDate('date_appr', '<=', $date_fin)->where('produit',$produit)->get()){
            $response = '';
            foreach ($approvisionnements as $approvision) {

                //On recupere le nom du produit grace a l'id correspondant au produit
                if($produit = Produit::findOrFail($approvision->produit)){
                    $approvision->nom_prod = $produit->nom_produit;
                }
                //On recupere le nom du fournisseur grace a l'id correspondant au nom du fournisseur
                if($fournisseur = Fournisseur::findOrFail($approvision->fournisseur)){
                    $approvision->nom_fournisseur = $fournisseur->nom_fournisseur;
                }
                //On construit le corps du tableau d'historique avec les donnees recueillis
                $response.='
                            <tr>
                                <td>'.$approvision->num_BL.'</td>
                                <td>'.$approvision->nom_prod.'</td>
                                <td>'.$approvision->conditionnement.'</td>
                                <td>'.$approvision->qte_cond.'</td>
                                <td>'.$approvision->total.'</td>
                                <td>'.$approvision->nom_fournisseur.'</td>
                                <td>'.$approvision->date_peremption.'</td>
                            </tr>
                ';
            }
        }
        return $response;
    }
}
