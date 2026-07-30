<?php
namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UploadLetterDocumentsRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return ['documents' => ['required', 'array'], 'documents.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120']];
    }
}
