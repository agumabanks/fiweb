<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

class PdfController extends Controller
{
    public function generatePdf()
    {
        Pdf::view('pdf')
            ->format('a4')
            ->save('test.pdf');

        return redirect('/');
    }
}