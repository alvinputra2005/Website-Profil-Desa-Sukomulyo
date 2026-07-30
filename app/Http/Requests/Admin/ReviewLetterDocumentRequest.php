<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
class ReviewLetterDocumentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['review_status' => ['required', 'in:approved,rejected,pending'], 'review_note' => ['nullable', 'string', 'max:1000']]; }
}
