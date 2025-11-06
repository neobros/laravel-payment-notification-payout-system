<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentCsvRequest extends FormRequest {
  
  public function rules(): array {
    return [
      'file' => ['required','file','mimes:csv,txt','max:153600'], // 150MB
    ];
  }
  public function authorize(): bool { return true; }
}
