@props([
    'label' => null,
    'name' => 'file',
    'id' => null,
    'accept' => '.pdf,.doc,.docx',
    'maxSize' => '10 MB',
    'hint' => null,
    'required' => false
])

@php
    $inputId = $id ?? ($name . '_' . uniqid());
@endphp

<div x-data="{ 
        fileName: '', 
        fileSize: '', 
        isDropping: false,
        clear() {
            this.fileName = '';
            this.fileSize = '';
            $refs.fileInput.value = '';
        },
        handleFiles(files) {
            if (files && files.length > 0) {
                this.fileName = files[0].name;
                const bytes = files[0].size;
                if (bytes < 1024) this.fileSize = bytes + ' B';
                else if (bytes < 1048576) this.fileSize = (bytes / 1024).toFixed(1) + ' KB';
                else this.fileSize = (bytes / 1048576).toFixed(1) + ' MB';
            }
        }
    }" 
    class="w-full space-y-2">

    @if($label)
        <label class="block text-xs font-semibold text-on-surface">
            {{ $label }}
            @if($required)
                <span class="text-error font-bold">*</span>
            @endif
        </label>
    @endif

    <div @dragover.prevent="isDropping = true"
         @dragleave.prevent="isDropping = false"
         @drop.prevent="isDropping = false; handleFiles($event.dataTransfer.files); $refs.fileInput.files = $event.dataTransfer.files"
         :class="isDropping ? 'border-primary bg-primary-fixed/20' : 'border-outline/40 hover:border-primary/60 bg-surface-container-lowest'"
         class="relative border-2 border-dashed rounded-xl p-6 text-center transition-all cursor-pointer">
        
        <input type="file" 
               id="{{ $inputId }}"
               name="{{ $name }}"
               x-ref="fileInput"
               accept="{{ $accept }}"
               {{ $required ? 'required' : '' }}
               @change="handleFiles($event.target.files)"
               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />

        <div x-show="!fileName" class="space-y-2 pointer-events-none">
            <div class="w-12 h-12 mx-auto rounded-full bg-primary-fixed/40 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-[24px]">cloud_upload</span>
            </div>
            <div>
                <p class="text-xs font-bold text-on-surface">
                    <span class="text-primary hover:underline">Click to upload</span> or drag and drop
                </p>
                <p class="text-[11px] text-on-surface-variant mt-0.5">
                    Accepted formats: {{ $accept }} (Max: {{ $maxSize }})
                </p>
            </div>
        </div>

        <div x-show="fileName" class="flex items-center justify-between p-3 rounded-lg bg-surface-container-low border border-surface-container-highest pointer-events-auto" style="display: none;">
            <div class="flex items-center gap-3 overflow-hidden text-left">
                <span class="material-symbols-outlined text-primary text-[24px]">description</span>
                <div class="truncate">
                    <p class="text-xs font-bold text-on-surface truncate" x-text="fileName"></p>
                    <p class="text-[10px] text-on-surface-variant font-mono" x-text="fileSize"></p>
                </div>
            </div>
            <button type="button" @click.stop="clear()" class="p-1.5 rounded-md hover:bg-error-container/30 text-error transition" title="Remove file">
                <span class="material-symbols-outlined text-[18px]">delete</span>
            </button>
        </div>
    </div>

    @if($hint)
        <p class="text-[11px] text-on-surface-variant">{{ $hint }}</p>
    @endif
</div>

