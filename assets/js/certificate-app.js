/**
 * Pro Certificate Creator - Main Application
 */

class CertificateApp {
    constructor() {
        console.log('CertificateApp: Initializing...');
        this.canvas = document.getElementById('cert-canvas');
        if (!this.canvas) {
            console.error('Canvas element #cert-canvas not found!');
            return;
        }
        this.ctx = this.canvas.getContext('2d');
        this.state = {
            templateImage: null,
            names: [], // Array of { text: string, selected: boolean }
            currentNameIndex: 0,
            dragging: null,
            renderScale: 1,
            exporting: false,
            settings: {
                name: { x: 0.5, y: 0.5, size: 50, color: '#000000', font: 'Arial', align: 'center', shadow: false },
                global: { fontFile: null }
            }
        };

        this.init();
    }

    init() {
        try {
            console.log('CertificateApp: Binding events...');
            this.bindEvents();
            this.resizeCanvas();
            window.addEventListener('resize', () => this.resizeCanvas());

            // Initial Draw
            this.draw();
            console.log('CertificateApp: Initialized successfully.');
        } catch (e) {
            console.error('CertificateApp: Initialization failed:', e);
            alert('App Initialization Failed. Check console for details.');
        }
    }

    bindEvents() {
        // Uploads
        const uploadTemplate = document.getElementById('upload-template');
        if (uploadTemplate) uploadTemplate.addEventListener('change', (e) => this.handleTemplateUpload(e));

        const uploadFont = document.getElementById('upload-font');
        if (uploadFont) uploadFont.addEventListener('change', (e) => this.handleFontUpload(e));

        // Inputs
        const namesInput = document.getElementById('names-input');
        if (namesInput) namesInput.addEventListener('input', (e) => this.parseNames(e.target.value));

        // Styling Controls
        this.bindControl('font-size', 'name.size');
        this.bindControl('font-color', 'name.color');

        const fontFamily = document.getElementById('font-family');
        if (fontFamily) fontFamily.addEventListener('change', (e) => {
            this.state.settings.name.font = e.target.value;
            this.draw();
        });

        // Canvas Interactions
        this.canvas.addEventListener('mousedown', (e) => this.handleMouseDown(e));
        this.canvas.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        this.canvas.addEventListener('mouseup', () => this.state.dragging = null);
        this.canvas.addEventListener('touchstart', (e) => this.handleTouchStart(e)); // Mobile support
        this.canvas.addEventListener('touchmove', (e) => this.handleTouchMove(e));

        // Selection Actions
        const btnSelectAll = document.getElementById('btn-select-all');
        if (btnSelectAll) btnSelectAll.addEventListener('click', () => this.toggleAllSelection(true));

        const btnDeselectAll = document.getElementById('btn-deselect-all');
        if (btnDeselectAll) btnDeselectAll.addEventListener('click', () => this.toggleAllSelection(false));

        // Export Actions
        const btnExportSelected = document.getElementById('btn-export-selected');
        if (btnExportSelected) btnExportSelected.addEventListener('click', () => this.exportBatch('selected'));

        const btnExportCurrent = document.getElementById('btn-export-current');
        if (btnExportCurrent) btnExportCurrent.addEventListener('click', () => this.exportCurrent());
    }

    bindControl(id, path) {
        const el = document.getElementById(id);
        if (!el) return;

        el.addEventListener('input', (e) => {
            const keys = path.split('.');
            if (keys.length === 2) {
                this.state.settings[keys[0]][keys[1]] = e.target.type === 'number' || e.target.type === 'range' ? parseFloat(e.target.value) : e.target.value;
            }
            this.draw();
        });
    }

    resizeCanvas() {
        const container = this.canvas.parentElement;
        const rect = container.getBoundingClientRect();
        if (this.canvas.width > 0) {
            this.state.renderScale = rect.width / this.canvas.width;
        }
    }

    // --- File Handling ---

    async handleTemplateUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        console.log('Loading template:', file.name);

        if (file.type === 'application/pdf') {
            await this.loadPdfTemplate(file);
        } else {
            this.loadImageTemplate(file);
        }
    }

    loadImageTemplate(file) {
        const reader = new FileReader();
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                this.canvas.width = img.width;
                this.canvas.height = img.height;
                this.state.templateImage = img;

                // Reset positions to center if it's first load
                this.state.settings.name.x = img.width / 2;
                this.state.settings.name.y = img.height / 2;
                this.resizeCanvas();
                this.draw();
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    async loadPdfTemplate(file) {
        // Requires pdf.js
        if (typeof pdfjsLib === 'undefined') {
            alert('PDF Library not loaded!');
            return;
        }

        try {
            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
            const page = await pdf.getPage(1);

            const viewport = page.getViewport({ scale: 2.0 }); // High quality
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            await page.render({ canvasContext: context, viewport: viewport }).promise;

            // Convert to image
            const img = new Image();
            img.onload = () => {
                this.canvas.width = img.width;
                this.canvas.height = img.height;
                this.state.templateImage = img;

                this.state.settings.name.x = img.width / 2;
                this.state.settings.name.y = img.height / 2;
                this.resizeCanvas();
                this.draw();
            };
            img.src = canvas.toDataURL('image/jpeg');
        } catch (e) {
            console.error('PDF Load Error:', e);
            alert('Error loading PDF. See console.');
        }
    }

    async handleFontUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        console.log('Attempting to load font:', file.name);
        const fontName = 'CustomFont_' + Date.now();

        try {
            const buffer = await file.arrayBuffer();
            const font = new FontFace(fontName, buffer);
            const loadedFont = await font.load();
            document.fonts.add(loadedFont);

            const select = document.getElementById('font-family');
            if (select) {
                const option = document.createElement('option');
                option.value = fontName;
                option.text = file.name;
                select.add(option);
                select.value = fontName;
            }

            this.state.settings.name.font = fontName;
            this.draw();
            console.log('Font loaded successfully:', fontName);
        } catch (err) {
            console.error('Font load failed:', err);
            alert('Failed to load font: ' + err.message + '. Please ensure it is a valid TTF/OTF/WOFF file.');
        }
    }

    // --- Data Management ---

    parseNames(text) {
        this.state.names = text.split('\n').filter(n => n.trim() !== '').map(name => ({ text: name.trim(), selected: true }));
        this.renderNamesList();
        if (this.state.names.length > 0) {
            this.state.currentNameIndex = 0;
        }
        this.draw();
    }

    renderNamesList() {
        const container = document.getElementById('names-list-preview');
        if (!container) return;

        container.innerHTML = '';
        this.state.names.forEach((item, idx) => {
            const div = document.createElement('div');
            div.className = `name-item ${idx === this.state.currentNameIndex ? 'active' : ''}`;

            // Checkbox
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = item.selected;
            checkbox.onclick = (e) => {
                e.stopPropagation();
                item.selected = checkbox.checked;
            };

            // Label
            const span = document.createElement('span');
            span.textContent = item.text;
            span.style.flex = '1';

            div.appendChild(checkbox);
            div.appendChild(span);

            div.onclick = () => {
                this.state.currentNameIndex = idx;
                document.querySelectorAll('.name-item').forEach(el => el.classList.remove('active'));
                div.classList.add('active');
                this.draw();
            };
            container.appendChild(div);
        });

        const badge = document.getElementById('count-badge');
        if (badge) badge.textContent = this.state.names.length;
    }

    toggleAllSelection(selected) {
        this.state.names.forEach(n => n.selected = selected);
        this.renderNamesList();
    }

    // --- Interaction ---

    getMousePos(e) {
        const rect = this.canvas.getBoundingClientRect();
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;

        let clientX = e.clientX;
        let clientY = e.clientY;

        if (e.touches && e.touches.length > 0) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        }

        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    isHit(pos, obj) {
        const tolerance = 50;
        return Math.abs(pos.x - obj.x) < tolerance && Math.abs(pos.y - obj.y) < tolerance;
    }

    handleMouseDown(e) {
        const pos = this.getMousePos(e);

        if (this.isHit(pos, { x: this.state.settings.name.x, y: this.state.settings.name.y })) {
            this.state.dragging = 'name';
            return;
        }
    }

    handleMouseMove(e) {
        const pos = this.getMousePos(e);

        if (this.state.dragging) {
            e.preventDefault();
            this.canvas.style.cursor = 'grabbing';
            if (this.state.dragging === 'name') {
                this.state.settings.name.x = pos.x;
                this.state.settings.name.y = pos.y;
            }
            this.draw();
            return;
        }

        // Hover detection for cursor
        if (this.isHit(pos, { x: this.state.settings.name.x, y: this.state.settings.name.y })) {
            this.canvas.style.cursor = 'grab';
        } else {
            this.canvas.style.cursor = 'default';
        }
    }

    handleTouchStart(e) { this.handleMouseDown(e); }
    handleTouchMove(e) { this.handleMouseMove(e); }

    // --- Rendering ---

    draw() {
        const { width, height } = this.canvas;
        this.ctx.clearRect(0, 0, width, height);

        // 1. Draw Template
        if (this.state.templateImage) {
            this.ctx.drawImage(this.state.templateImage, 0, 0, width, height);
        } else {
            this.ctx.fillStyle = '#f0f0f0';
            this.ctx.fillRect(0, 0, width, height);
            this.ctx.fillStyle = '#ccc';
            this.ctx.font = '30px Arial';
            this.ctx.textAlign = 'center';
            this.ctx.fillText('Upload a Template', width / 2, height / 2);
        }

        const item = this.state.names[this.state.currentNameIndex];
        const currentName = item ? item.text : 'Abdullah Mohamed';

        // 2. Draw Name
        const sName = this.state.settings.name;
        this.ctx.font = `${sName.size}px "${sName.font}"`;
        this.ctx.fillStyle = sName.color;
        this.ctx.textAlign = 'center';
        this.ctx.textBaseline = 'middle';

        // Auto-detect RTL for Arabic
        const isArabic = /[\u0600-\u06FF]/.test(currentName);
        this.ctx.direction = isArabic ? 'rtl' : 'ltr';

        if (sName.shadow) {
            this.ctx.shadowColor = "rgba(0,0,0,0.5)";
            this.ctx.shadowBlur = 10;
        } else {
            this.ctx.shadowColor = "transparent";
        }

        this.ctx.fillText(currentName, sName.x, sName.y);
        this.ctx.shadowColor = "transparent";

        // Draw Controls (Only if not exporting)
        if (!this.state.exporting) {
            this.drawSelectionBox(currentName, sName.x, sName.y, sName.size);
            this.drawHandle(sName.x, sName.y, '#00bcd4');

            // Cursor update based on position (handled in mouse move, but ensures default here)
            this.canvas.style.cursor = 'default';
        }
    }

    drawSelectionBox(text, x, y, fontSize) {
        const metrics = this.ctx.measureText(text);
        const width = metrics.width;
        const height = fontSize; // Approximate
        const padding = 10;

        this.ctx.save();
        this.ctx.strokeStyle = 'rgba(0, 188, 212, 0.5)';
        this.ctx.lineWidth = 1;
        this.ctx.setLineDash([5, 5]);
        this.ctx.strokeRect(x - width / 2 - padding, y - height / 2 - padding, width + padding * 2, height + padding * 2);
        this.ctx.restore();
    }

    drawHandle(x, y, color) {
        this.ctx.beginPath();
        this.ctx.arc(x, y, 8, 0, 2 * Math.PI);
        this.ctx.fillStyle = color;
        this.ctx.fill();
        this.ctx.strokeStyle = '#fff';
        this.ctx.lineWidth = 2;
        this.ctx.stroke();
    }

    // --- Export ---

    async exportCurrent() {
        // Export just the current canvas view
        const link = document.createElement('a');
        link.download = 'certificate_preview.jpg';
        link.href = this.canvas.toDataURL();
        link.click();
    }

    async exportBatch(mode) {
        const btn = document.getElementById('btn-export-selected');
        const defaultText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        btn.disabled = true;

        const format = document.getElementById('export-format').value; // 'jpg' or 'pdf'
        const zip = new JSZip();

        // Filter Items
        const targets = this.state.names.filter(n => mode === 'selected' ? n.selected : true);

        if (targets.length === 0) {
            alert('No names selected!');
            btn.innerHTML = defaultText;
            btn.disabled = false;
            return;
        }

        this.state.exporting = true;

        try {
            // Processing
            for (const item of targets) {
                const sName = this.state.settings.name;
                const name = item.text;

                // Draw Clean (handled by this.draw with exporting flag, but we need to force it)
                // Actually, duplicate draw logic here is safer for batch to ensure speed/sync
                const { width, height } = this.canvas;
                this.ctx.clearRect(0, 0, width, height);
                this.ctx.drawImage(this.state.templateImage, 0, 0, width, height);

                this.ctx.font = `${sName.size}px "${sName.font}"`;
                this.ctx.fillStyle = sName.color;
                this.ctx.textAlign = 'center';
                this.ctx.textBaseline = 'middle';

                // Auto-detect RTL for Export
                const isArabic = /[\u0600-\u06FF]/.test(name);
                this.ctx.direction = isArabic ? 'rtl' : 'ltr';

                this.ctx.shadowColor = sName.shadow ? "rgba(0,0,0,0.5)" : "transparent";
                this.ctx.shadowBlur = sName.shadow ? 10 : 0;

                this.ctx.fillText(name, sName.x, sName.y);
                this.ctx.shadowColor = "transparent";

                if (format === 'pdf') {
                    // Generate PDF using jsPDF
                    if (typeof jspdf === 'undefined') throw new Error('jspdf not loaded');

                    const orientation = width > height ? 'l' : 'p';

                    // @ts-ignore
                    const pdf = new jspdf.jsPDF({
                        orientation: orientation,
                        unit: 'px',
                        format: [width, height]
                    });

                    const imgData = this.canvas.toDataURL('image/jpeg', 0.95);
                    pdf.addImage(imgData, 'JPEG', 0, 0, width, height);

                    // Get Blob
                    const pdfBlob = pdf.output('blob');
                    zip.file(`${name}.pdf`, pdfBlob);

                } else {
                    // JPG
                    const blob = await new Promise(resolve => this.canvas.toBlob(resolve, 'image/jpeg', 0.9));
                    zip.file(`${name}.jpg`, blob);
                }
            }

            const content = await zip.generateAsync({ type: "blob" });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(content);
            link.download = `certificates_${format}.zip`;
            link.click();

        } catch (e) {
            console.error('Export Error:', e);
            alert('An error occurred during export. See console.');
        } finally {
            this.state.exporting = false;
            btn.innerHTML = defaultText;
            btn.disabled = false;

            // Restore preview (draw handles again)
            this.draw();
        }
    }
}

// Initialize on Load
document.addEventListener('DOMContentLoaded', () => {
    window.certApp = new CertificateApp();
});
