import { Controller } from '@hotwired/stimulus';
import Quill from 'quill';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        uploadUrl: String
    }

    connect() {
        // Create editor container
        const editorContainer = document.createElement('div');
        editorContainer.className = 'quill-editor-container';
        this.element.parentNode.insertBefore(editorContainer, this.element);

        // Initialize Quill
        this.quill = new Quill(editorContainer, {
            theme: 'snow',
            placeholder: '',
            modules: {
                syntax: {
                    highlight: true,
                    // Configure available languages to match the custom build
                    languages: [
                        { key: 'bash', label: 'Bash' },
                        { key: 'css', label: 'CSS' },
                        { key: 'diff', label: 'Diff' },
                        { key: 'dockerfile', label: 'Dockerfile' },
                        { key: 'go', label: 'Go' },
                        { key: 'ini', label: 'INI' },
                        { key: 'javascript', label: 'JavaScript' },
                        { key: 'json', label: 'JSON' },
                        { key: 'makefile', label: 'Makefile' },
                        { key: 'markdown', label: 'Markdown' },
                        { key: 'nginx', label: 'Nginx' },
                        { key: 'pgsql', label: 'PostgreSQL' },
                        { key: 'php', label: 'PHP' },
                        { key: 'php-template', label: 'PHP Template' },
                        { key: 'plaintext', label: 'Plain Text' },
                        { key: 'properties', label: 'Properties' },
                        { key: 'rust', label: 'Rust' },
                        { key: 'shell', label: 'Shell' },
                        { key: 'sql', label: 'SQL' },
                        { key: 'twig', label: 'Twig' },
                        { key: 'typescript', label: 'TypeScript' },
                        { key: 'wasm', label: 'WebAssembly' },
                        { key: 'yaml', label: 'YAML' }
                    ]
                },
                toolbar: {
                    container: [
                        [{ 'header': [ 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image'],
                        ['clean']
                    ],
                    handlers: {
                        'image': () => this.imageHandler()
                    }
                }
            }
        });

        // Set initial content if exists
        if (this.element.value) {
            this.quill.root.innerHTML = this.element.value;
        }

        // Update textarea on content change
        this.quill.on('text-change', () => {
            this.element.value = this.quill.root.innerHTML;
        });

        // Handle image paste
        this.quill.root.addEventListener('paste', (e) => this.handlePaste(e));

        // Handle image drop
        this.quill.root.addEventListener('drop', (e) => this.handleDrop(e));

        // Allow editing alt text by clicking images
        this.quill.root.addEventListener('click', (e) => {
            if (e.target.tagName === 'IMG') {
                this.editImageAlt(e.target);
            }
        });
    }

    imageHandler() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = async () => {
            const file = input.files[0];
            if (file) {
                await this.uploadImage(file);
            }
        };
    }

    async handlePaste(event) {
        const items = event.clipboardData?.items;
        if (!items) return;

        for (const item of items) {
            if (item.type.startsWith('image/')) {
                event.preventDefault();
                const file = item.getAsFile();
                if (file) {
                    await this.uploadImage(file);
                }
            }
        }
    }

    async handleDrop(event) {
        event.preventDefault();
        const files = event.dataTransfer?.files;
        if (!files || files.length === 0) return;

        for (const file of files) {
            if (file.type.startsWith('image/')) {
                await this.uploadImage(file);
            }
        }
    }

    async uploadImage(file) {
        // Show loading indicator
        const range = this.quill.getSelection(true);
        this.quill.insertText(range.index, 'Uploading image...');

        const formData = new FormData();
        formData.append('image', file);

        try {
            const response = await fetch(this.uploadUrlValue, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error('Upload failed');
            }

            const data = await response.json();

            // Remove loading text
            this.quill.deleteText(range.index, 'Uploading image...'.length);

            // Insert image
            const altText = prompt('Enter alt text for this image (you can edit it later by clicking the image):', '');

            this.quill.insertEmbed(range.index, 'image', data.url);

            const img = this.quill.root.querySelector(`img[src="${data.url}"]`);
            img.setAttribute('loading', 'lazy');
            if (altText) {
                if (img) {
                    img.setAttribute('alt', altText);
                }
            }

            this.quill.setSelection(range.index + 1);
        } catch (error) {
            console.error('Image upload failed:', error);

            // Remove loading text
            this.quill.deleteText(range.index, 'Uploading image...'.length);

            alert('Failed to upload image. Please try again.');
        }
    }

    editImageAlt(img) {
        const currentAlt = img.getAttribute('alt') || '';
        const newAlt = prompt('Enter alt text for this image:', currentAlt);

        if (newAlt !== null) {  // null means cancelled
            img.setAttribute('alt', newAlt);
            // Trigger change event so the textarea updates
            this.quill.root.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    disconnect() {
        if (this.quill) {
            this.quill.off('text-change');
        }
    }
}
