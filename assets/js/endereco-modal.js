/**
 * Classe para gerenciar modal de endereços
 */
class EnderecoModal {
    constructor() {
        this.modal = document.getElementById('modalEndereco');
        this.form = document.getElementById('formEnderecoModal');
        this.config = window.FormConfig || {};
    }

    static init() {
        const instance = new EnderecoModal();
        if (instance.modal && instance.form) {
            instance.setupEventListeners();
        }
        return instance;
    }

    setupEventListeners() {
        // Buscar CEP
        this.setupCepSearch();
        
        // Submissão do formulário
        this.setupFormSubmission();
        
        // Limpeza ao fechar modal
        this.setupModalCleanup();
    }

    setupCepSearch() {
        const btnBuscar = document.getElementById('btnBuscarCepModal');
        const cepInput = document.getElementById('cepModal');
        
        if (btnBuscar && cepInput) {
            btnBuscar.addEventListener('click', () => this.buscarCep());
            
            cepInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.buscarCep();
                }
            });

            // Formatação automática do CEP
            cepInput.addEventListener('input', this.formatCep);
        }
    }

    formatCep(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        e.target.value = value;
    }

    async buscarCep() {
        const cepInput = document.getElementById('cepModal');
        const btnBuscar = document.getElementById('btnBuscarCepModal');
        const status = document.getElementById('cepStatusModal');
        
        const cep = cepInput.value.replace(/\D/g, '');
        
        if (cep.length !== 8) {
            this.showCepStatus('CEP deve ter 8 dígitos', 'danger');
            return;
        }

        // Loading state
        this.showCepStatus('Buscando endereço...', 'primary');
        btnBuscar.disabled = true;
        btnBuscar.innerHTML = '<i class="spinner-border spinner-border-sm"></i>';

        try {
            const response = await fetch(`${this.config.endpoints?.cep}?cep=${cep}`);
            const data = await response.json();

            if (data.success && data.endereco) {
                this.preencherCamposEndereco(data.endereco);
                this.showCepStatus('Endereço preenchido automaticamente!', 'success');
                document.getElementById('numeroModal').focus();
            } else {
                this.showCepStatus(data.message || 'CEP não encontrado', 'warning');
            }
        } catch (error) {
            console.error('Erro:', error);
            this.showCepStatus('Erro ao buscar CEP. Verifique sua conexão.', 'danger');
        } finally {
            btnBuscar.disabled = false;
            btnBuscar.innerHTML = '<i class="bi bi-search"></i>';
        }
    }

    preencherCamposEndereco(endereco) {
        const campos = [
            { id: 'logradouroModal', key: 'logradouro' },
            { id: 'bairroModal', key: 'bairro' },
            { id: 'cidadeModal', key: 'cidade' },
            { id: 'estadoModal', key: 'estado' }
        ];

        campos.forEach(campo => {
            const element = document.getElementById(campo.id);
            if (element) {
                element.value = endereco[campo.key] || '';
            }
        });
    }

    showCepStatus(message, type) {
        const status = document.getElementById('cepStatusModal');
        if (status) {
            status.textContent = message;
            status.className = `text-${type} small mt-1`;
        }
    }

    setupFormSubmission() {
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.submitForm();
        });
    }

    async submitForm() {
        const btnSalvar = document.getElementById('btnSalvarEndereco');
        const alertModal = document.getElementById('alertModal');

        // Validar formulário
        if (!this.validateForm()) {
            return;
        }

        // Loading state
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Salvando...';
        alertModal.style.display = 'none';

        try {
            const formData = new FormData(this.form);
            const response = await fetch(this.config.endpoints?.endereco, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.sucesso) {
                this.showModalAlert('success', data.mensagem);
                
                if (data.endereco) {
                    this.addEnderecoToSelect(data.endereco);
                }

                setTimeout(() => this.closeModal(), 2000);
            } else {
                this.showModalAlert('danger', data.mensagem || 'Erro ao salvar endereço');
            }
        } catch (error) {
            console.error('Erro:', error);
            this.showModalAlert('danger', `Erro ao salvar endereço: ${error.message}`);
        } finally {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="bi bi-save me-2"></i>Salvar Endereço';
        }
    }

    validateForm() {
        const requiredFields = [
            { name: 'cep', label: 'CEP' },
            { name: 'logradouro', label: 'Logradouro' },
            { name: 'numero', label: 'Número' },
            { name: 'bairro', label: 'Bairro' },
            { name: 'cidade', label: 'Cidade' },
            { name: 'estado', label: 'Estado' }
        ];

        let isValid = true;
        const errors = [];

        requiredFields.forEach(field => {
            const input = this.form.querySelector(`input[name="${field.name}"]`);
            if (!input || !input.value.trim()) {
                errors.push(field.label);
                if (input) input.classList.add('is-invalid');
                isValid = false;
            } else {
                if (input) input.classList.remove('is-invalid');
            }
        });

        // Validações específicas
        const cep = this.form.querySelector('input[name="cep"]').value.replace(/\D/g, '');
        if (cep.length !== 8) {
            errors.push('CEP deve ter 8 dígitos');
            isValid = false;
        }

        const estado = this.form.querySelector('input[name="estado"]').value.trim();
        if (estado.length !== 2) {
            errors.push('Estado deve ter 2 caracteres');
            isValid = false;
        }

        if (!isValid) {
            this.showModalAlert('danger', `Preencha os campos obrigatórios: ${errors.join(', ')}`);
        }

        return isValid;
    }

    showModalAlert(type, message) {
        const alertModal = document.getElementById('alertModal');
        if (alertModal) {
            alertModal.className = `alert alert-${type} alert-dismissible fade show`;
            alertModal.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertModal.style.display = 'block';
        }
    }

    addEnderecoToSelect(endereco) {
        const select = document.getElementById('endereco_id');
        if (!select || !endereco) return;

        const option = document.createElement('option');
        option.value = endereco.id;
        option.selected = true;
        
        const texto = `${endereco.logradouro}, ${endereco.numero} - ${endereco.cidade}/${endereco.estado}`;
        option.textContent = endereco.principal ? `${texto} (Principal)` : texto;
        
        select.appendChild(option);

        // Feedback visual
        select.classList.add('border-success');
        setTimeout(() => select.classList.remove('border-success'), 3000);

        // Atualizar resumo se disponível
        if (window.formSolicitacaoInstance) {
            window.formSolicitacaoInstance.updateSummary();
        }
    }

    closeModal() {
        const modal = bootstrap.Modal.getInstance(this.modal);
        if (modal) {
            modal.hide();
        }
    }

    setupModalCleanup() {
        this.modal.addEventListener('hidden.bs.modal', () => {
            this.form.reset();
            document.getElementById('alertModal').style.display = 'none';
            document.getElementById('cepStatusModal').textContent = '';
            
            // Remover classes de validação
            this.form.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
        });
    }
}

// Expor para uso global
window.EnderecoModal = EnderecoModal;
