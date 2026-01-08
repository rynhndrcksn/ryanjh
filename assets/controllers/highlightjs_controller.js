import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.highlight();
    }

    highlight() {
        if (typeof window.hljs === 'undefined') {
            console.warn('Highlight.js not loaded');
            return;
        }

        this.element.querySelectorAll('pre[data-language]').forEach((block) => {
            // Skip if already highlighted
            if (block.classList.contains('hljs') || block.querySelector('code.hljs')) {
                return;
            }

            const language = block.getAttribute('data-language');

            const code = document.createElement('code');
            code.className = `language-${language}`;
            code.textContent = block.textContent;

            block.textContent = '';
            block.appendChild(code);

            window.hljs.highlightElement(code);
        });
    }
}
