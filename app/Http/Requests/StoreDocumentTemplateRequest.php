<?php

namespace App\Http\Requests;

use App\Models\DocumentTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Unggah berkas unduhan (kelompok 2). Dokumen terbit tidak menerima unggahan. */
class StoreDocumentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_event' => ['required', 'integer', 'exists:event,id_event'],
            'jenis_dokumen' => ['required', Rule::in(DocumentTemplate::DOWNLOADS)],
            'nama_template' => ['required', 'string', 'max:150'],
            'subjenis' => ['nullable', Rule::in(DocumentTemplate::SUBJENIS)],
            'bahasa' => ['nullable', Rule::in(['id', 'en'])],
            'versi' => ['nullable', 'string', 'max:20', 'regex:/^[0-9A-Za-z.\-]+$/'],
            'file' => ['required', 'file', 'mimes:docx,doc,pptx,ppt,pdf', 'max:10240'],
        ];
    }
}
