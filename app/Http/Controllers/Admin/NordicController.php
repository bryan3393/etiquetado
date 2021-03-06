<?php

namespace App\Http\Controllers\Admin;

use App\Models\Lote;
use App\Models\OrdenProduccion;
use App\Models\Producto;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class NordicController extends Controller
{
    public function print_etiqueta($orden_id, $producto_id, $fecha)
    {
        $orden = OrdenProduccion::findOrFail($orden_id);
        $lote = $orden->lote;
        $producto = Producto::findOrFail($producto_id);

        $data['fecha_produccion'] = \Carbon\Carbon::createFromTimestamp($fecha)->format('d.m.Y');
        $data['fecha_vencimiento'] = \Carbon\Carbon::createFromFormat('Y-m-d', $lote->lote_fecha_expiracion)->format('d.m.Y');
        $data['trim'] = $producto->trim->trim_nombre;
        $data['calibre'] = $producto->calibre->calibre_nombre;
        $data['calidad'] = $producto->calidad->calidad_nombre;
        $data['v2'] = \Config::get('producto.v2')[$producto->producto_v2_id];
        $data['producto_id'] = $producto->producto_id;
        $data['orden_number'] = $orden->orden_id;
        $data['lote_number'] = $lote->lote_id;
        //dd($data);

        $view =  \View::make('admin.nordic.invoice',
            compact('data'))->render();

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($view);

        return $pdf->stream('invoice', array ("Attachment" => false));

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('admin.nordic.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \DB::beginTransaction();

        try{
            $lote = Lote::find($request->lote_id);

            $orden = OrdenProduccion::findOrFail($request->orden_id);

            if($lote->lote_id != $orden->orden_lote_id)
                return null;

            $orden_producto_id = $orden->productos()
                ->where('op_producto_producto_id',$request->orden_productos)
                ->firstOrFail()->producto_id;

            $fecha = \Carbon\Carbon::createFromFormat('d-m-Y', $request->etiqueta_fecha)->timestamp;

            $resp = ["estado" => "ok",
                "orden_id" => $orden->orden_id,
                "producto_id" => $orden_producto_id,
                "fecha" => $fecha];

        }
        catch ( Exception $e ){
            \DB::rollback();
            $resp = ["estado" => "nok"];
        }

        \DB::commit();

        return response()->json($resp);
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
}
