/**
 * Classe para gerenciar formulário de solicitação
 */
class FormSolicitacao {
    constructor() {
        this.selectedFiles = [];
        this.config = window.FormConfig || {};
        this.form = document.getElementById('formSolicitacao');
        this.uploadArea = document.getElementById('uploadArea');
        this.previewContainer = document.getElementById('preview-container');
        
        // Bind methods
        this.updateCharCounters = this.updateCharCounters.bind(this);
        this.updateSummary = this.updateSummary.bind(this);
        this.handleFiles = this.handleFiles.bind(this);
    }

    static init() {
        const instance = new FormSolicitacao();
        instance.setupEventListeners();
        instance.initializeCounters();
        instance.updateSummary();
        return instance;
    }

    setupEventListeners() {
        // Contadores de caracteres
        this.setupCharCounters();
        
        // Resumo dinâmico
        this.setupSummaryUpdaters();
        
        // Upload de arquivos
        this.setupFileUpload();
        
        // Validação do formulário
        this.setupFormValidation();
    }

    setupCharCounters() {
        const fields = ['titulo', 'descricao'];
        
        fields.forEach(fieldName => {
            const input = document.getElementById(fieldName);
            const counter = document.querySelector(`[data-counter="${fieldName}"]`);
            
            if (input && counter) {
                input.addEventListener('input', () => this.updateCharCounter(input, counter));
                // Inicializar contador
                this.updateCharCounter(input, counter);
            }
        });
    }

    updateCharCounter(input, counter) {
        const length = input.value.length;
        const maxLength = input.getAttribute('maxlength');
        
        counter.textContent = length;
        
        // Feedback visual
        const container = counter.closest('.char-counter');
        container.classList.toggle('text-warning', length > maxLength * 0.8);
        container.classList.toggle('text-danger', length > maxLength * 0.95);
    }

    setupSummaryUpdaters() {
        const fields = ['tipo_servico_id', 'endereco_id', 'urgencia', 'orcamento_estimado', 'data_atendimento'];
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                const eventType = field.tagName === 'SELECT' ? 'change' : 'input';
                field.addEventListener(eventType, this.updateSummary);
            }
        });
    }

    updateSummary() {
        const summaryUpdaters = {
            'summary-tipo': () => this.getSummaryText('tipo_servico_id', 'Não selecionado'),
            'summary-urgencia': () => this.getUrgenciaSummary(),
            'summary-endereco': () => this.getEnderecoSummary(),
            'summary-orcamento': () => this.getOrcamentoSummary(),
            'summary-data': () => this.getDataSummary(),
            'summary-fotos': () => `${this.selectedFiles.length} anexada${this.selectedFiles.length !== 1 ? 's' : ''}`
        };

        Object.entries(summaryUpdaters).forEach(([elementId, updater]) => {
            const element = document.getElementById(elementId);
            if (element) {
                const result = updater();
                if (typeof result === 'object') {
                    element.textContent = result.text;
                    element.className = result.className;
                } else {
                    element.textContent = result;
                }
            }
        });
    }

    getSummaryText(fieldId, defaultText) {
        const field = document.getElementById(fieldId);
        return field?.value ? field.options[field.selectedIndex].text : defaultText;
    }

    getUrgenciaSummary() {
        const urgencia = document.getElementById('urgencia');
        if (!urgencia?.value) return { text: 'Média', className: 'badge bg-warning' };
        
        const urgenciaMap = {
            'baixa': { text: '🟢 Baixa', className: 'badge bg-success' },
            'media': { text: '🟡 Média', className: 'badge bg-warning' },
            'alta': { text: '🔴 Alta', className: 'badge bg-danger' }
        };
        
        return urgenciaMap[urgencia.value] || urgenciaMap['media'];
    }

    getEnderecoSummary() {
        const endereco = document.getElementById('endereco_id');
        if (!endereco?.value) return 'Não selecionado';
        
        const text = endereco.options[endereco.selectedIndex].text;
        return text.length > 30 ? text.substring(0, 30) + '...' : text;
    }

    getOrcamentoSummary() {
        const orcamento = document.getElementById('orcamento_estimado');
        if (!orcamento?.value) return 'A combinar';
        
        return 'R$ ' + parseFloat(orcamento.value).toLocaleString('pt-BR', {
            minimumFractionDigits: 2
        });
    }

    getDataSummary() {
        const data = document.getElementById('data_atendimento');
        if (!data?.value) return { text: 'Não informada', className: 'text-muted' };
        
        const dataFormatada = new Date(data.value).toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        return { text: dataFormatada, className: 'text-success' };
    }

    setupFileUpload() {
        if (!this.uploadArea) return;

        // Eventos do upload area
        this.uploadArea.addEventListener('click', () => {
            document.getElementById('imagens').click();
        });

        this.uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.uploadArea.classList.add('dragover');
        });

        this.uploadArea.addEventListener('dragleave', () => {
            this.uploadArea.classList.remove('dragover');
        });

        this.uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            this.uploadArea.classList.remove('dragover');
            this.handleFiles(e.dataTransfer.files);
        });

        // Input de arquivo
        const fileInput = document.getElementById('imagens');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                this.handleFiles(e.target.files);
            });
        }
    }

    handleFiles(files) {
        const maxFiles = this.config.upload?.maxFiles || 5;
        const maxSize = this.config.upload?.maxSize || 5 * 1024 * 1024;
        
        if (files.length + this.selectedFiles.length > maxFiles) {
            this.showAlert(`Máximo ${maxFiles} imagens permitidas!`, 'warning');
            return;
        }

        Array.from(files).forEach(file => {
            if (this.validateFile(file, maxSize)) {
                this.selectedFiles.push(file);
                this.createPreview(file);
            }
        });

        this.updateFileInput();
        this.updateSummary();
    }

    validateFile(file, maxSize) {
        const allowedTypes = this.config.upload?.allowedTypes || ['image/jpeg', 'image/jpg', 'image/png'];
        
        if (!file.type.startsWith('image/')) {
            this.showAlert(`Arquivo deve ser uma imagem: ${file.name}`, 'danger');
            return false;
        }

        if (!allowedTypes.includes(file.type)) {
            this.showAlert(`Tipo de arquivo não permitido: ${file.name}`, 'danger');
            return false;
        }

        if (file.size > maxSize) {
            const maxMB = (maxSize / (1024 * 1024)).toFixed(1);
            this.showAlert(`Arquivo muito grande: ${file.name} (máx. ${maxMB}MB)`, 'danger');
            return false;
        }

        return true;
    }

    createPreview(file) {
        if (!this.previewContainer) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const col = document.createElement('div');
            col.className = 'col-3';
            col.innerHTML = `
                <div class="preview-image position-relative border rounded overflow-hidden">
                    <img src="${e.target.result}" alt="Preview" style="width: 100%; height: 80px; object-fit: cover;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-1" 
                            onclick="FormSolicitacao.removeFile('${file.name}')" style="border-radius: 50%; width: 25px; height: 25px;">
                        <i class="bi bi-x" style="font-size: 12px;"></i>
                    </button>
                </div>
            `;
            this.previewContainer.appendChild(col);
            this.previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    static removeFile(fileName) {
        const instance = window.formSolicitacaoInstance;
        if (!instance) return;

        instance.selectedFiles = instance.selectedFiles.filter(f => f.name !== fileName);
        
        // Remove preview
        const previews = instance.previewContainer.querySelectorAll('.col-3');
        previews.forEach(preview => {
            const btn = preview.querySelector('button');
            if (btn && btn.getAttribute('onclick').includes(fileName)) {
                preview.remove();
            }
        });

        if (instance.selectedFiles.length === 0) {
            instance.previewContainer.style.display = 'none';
        }

        instance.updateFileInput();
        instance.updateSummary();
    }

    updateFileInput() {
        const fileInput = document.getElementById('imagens');
        if (!fileInput) return;

        const dt = new DataTransfer();
        this.selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    }

    setupFormValidation() {
        if (!this.form) return;

        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                e.stopPropagation();
                this.showAlert('Por favor, corrija os erros no formulário antes de continuar.', 'danger');
            } else {
                this.showSubmitLoading();
            }
        });
    }

    validateForm() {
        const requiredFields = ['tipo_servico_id', 'endereco_id', 'titulo', 'descricao'];
        let isValid = true;

        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let message = '';

        // Reset previous validation
        field.classList.remove('is-valid', 'is-invalid');

        switch (field.id) {
            case 'tipo_servico_id':
            case 'endereco_id':
                isValid = value !== '';
                message = `Selecione um ${field.id.includes('tipo') ? 'tipo de serviço' : 'endereço'}`;
                break;

            case 'titulo':
                isValid = value.length >= 10 && value.length <= 100;
                message = 'O título deve ter entre 10 e 100 caracteres';
                break;

            case 'descricao':
                isValid = value.length >= 20 && value.length <= 1000;
                message = 'A descrição deve ter entre 20 e 1000 caracteres';
                break;
        }

        // Update UI
        field.classList.add(isValid ? 'is-valid' : 'is-invalid');
        
        const feedback = field.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = message;
        }

        return isValid;
    }

    showSubmitLoading() {
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Enviando...';
        }
    }

    showAlert(message, type = 'info') {
        // Implementação simples - pode ser melhorada com um sistema de alertas mais sofisticado
        console.log(`${type.toUpperCase()}: ${message}`);
        alert(message);
    }

    initializeCounters() {
        this.updateCharCounters();
        this.updateSummary();
    }
}

// Expor para uso global
window.FormSolicitacao = FormSolicitacao;

// Armazenar instância para métodos estáticos
document.addEventListener('DOMContentLoaded', () => {
    window.formSolicitacaoInstance = FormSolicitacao.init();
});
