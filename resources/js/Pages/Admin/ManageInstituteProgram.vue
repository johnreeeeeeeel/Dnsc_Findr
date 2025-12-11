<template>
    <AdminAppLayout title="Manage Institutes & Programs">
        <div class="container-fluid layout">

            <header class="header">
                <span class="title-container">
                    <p class="back-btn" @click="goBack">
                        <Icon class="icon" icon="mingcute:left-line" />
                    </p>
                    <h4 class="title">
                        Institutes & Programs List
                    </h4>
                </span>

                <div class="tools">
                    <div class="dropdown add-new-institute-program-dropdown">
                        <button type="button" class="btn" data-bs-toggle="dropdown" aria-expanded="false">
                            <Icon class="icon" icon="mingcute:add-fill" />
                            Add New
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" @click.prevent="openAddInstituteModal">
                                    <Icon class="icon" icon="mingcute:add-fill" /> New Institute
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="#" @click.prevent="openAddProgramModal">
                                    <Icon class="icon" icon="mingcute:add-fill" /> New Program
                                </a>
                            </li>
                            <li>
                                <hr>
                            </li>
                            <li>
                                <a class="dropdown-item special-item" href="#" @click.prevent="openAICompileModal">
                                    <Icon class="icon" icon="mingcute:sparkles-fill" />
                                    AI Compile from Text
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Loading -->
            <div v-if="loading" class="loading">
                <div class="spinner-border" role="status"></div>
                <div class="loading-text">Loading statistics...</div>
            </div>

            <!-- Institute and Program Lists -->
            <div v-else>
                <div class="card institute-program-card">

                    <div class="card-body">
                        <div v-for="inst in institutes" :key="inst.institute_id" class="lists">

                            <span class="institute">
                                <h6 class="institute-title">
                                    {{ inst.institute_code }} — {{ inst.institute_description }}
                                </h6>
                                <span class="institute-actions">
                                    <button class="btn btn-sm" @click="openEditInstitute(inst)">
                                        <Icon class="icon" icon="mingcute:edit-2-fill" />
                                        Edit
                                    </button>
                                    <button class="btn btn-sm" @click="confirmDeleteInstitute(inst.institute_id)">
                                        <Icon class="icon" icon="mingcute:delete-3-fill" />
                                        Delete
                                    </button>
                                </span>
                            </span>

                            <ul class="list-group list-group-flush program">
                                <li v-for="prog in inst.programs" :key="prog.program_id" class="list-group-item">
                                    <p class="program-title">
                                        <strong>{{ prog.program_code }}</strong> — {{ prog.program_description }}
                                    </p>
                                    <span class="program-actions">
                                        <button class="btn btn-sm" @click="openEditProgram(prog)">
                                            <Icon class="icon" icon="mingcute:edit-2-fill" />
                                            Edit
                                        </button>
                                        <button class="btn btn-sm" @click="confirmDeleteProgram(prog.program_id)">
                                            <Icon class="icon" icon="mingcute:delete-3-fill" />
                                            Delete
                                        </button>
                                    </span>
                                </li>
                                <li v-if="inst.programs.length === 0" class="list-group-item text-muted">No programs yet
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>

            <!-- AI Compile Modal -->

            <div class="modal fade primary-modal" id="aiCompileModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

                <div class="modal-dialog modal-dialog-centered modal-lg">

                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">
                                <Icon icon="mingcute:sparkles-fill" /> AI Compile Institutes & Programs
                            </h5>

                            <!-- Tutorial Popover Button -->
                            <a class="btn help-btn" id="aiCompilePopover" title="How to Use AI Compile">
                                ?
                            </a>


                        </div>

                        <div class="modal-body">
                            <div>
                                <label class="form-label">Paste Your Text Below:</label>
                                <textarea v-model="aiCompileText" class="form-control" rows="10" placeholder="// Example

Institute of Computing
Bachelor of Science in Information Technology
Bachelor of Science in Computer Science
Bachelor of Science in Information Systems
                                    ">
                                </textarea>
                            </div>

                            <div class="action-buttons">
                                <button class="btn btn-sm primary-btn" @click="compileWithAI"
                                    :disabled="!aiCompileText.trim() || aiCompiling">
                                    <span v-if="aiCompiling">Compiling...</span>
                                    <span v-else>
                                        <Icon icon="mingcute:sparkles-fill" />
                                        Compile with AI
                                    </span>
                                </button>
                            </div>

                            <!-- Preview Table -->

                            <div v-if="aiCompiledData && aiCompiledData.length > 0" class="table-responsive">

                                <div v-for="(item, index) in aiCompiledData" :key="index">

                                    <table class="table table-bordered">

                                        <thead>
                                            <tr>
                                                <th colspan="2">
                                                    {{ item.institute.name }}
                                                    <code class="ms-2">{{ item.institute.code }}</code>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th>Code</th>
                                                <th>Name</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr v-for="(prog, i) in item.programs" :key="i">
                                                <td><code>{{ prog.code }}</code></td>
                                                <td>{{ prog.name }}</td>
                                            </tr>

                                            <tr v-if="item.programs.length === 0">
                                                <td colspan="2">No programs</td>
                                            </tr>
                                        </tbody>

                                    </table>
                                </div>
                            </div>

                            <div v-else-if="aiError">
                                {{ aiError }}
                            </div>

                        </div>
                        <div class="modal-footer">
                            <div class="action-buttons">
                                <button type="button" class="btn btn-sm secondary-btn"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-sm primary-btn" @click="saveAICompiled"
                                    :disabled="!aiCompiledData || aiSaving">
                                    <span v-if="aiSaving">Saving...</span>
                                    <span v-else>Save All ({{ totalToCreate }} items)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success AI Compiled -->
            <div class="success-toast">
                <div id="aiCompiledSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-content">
                        <div class="toast-body">
                            <Icon class="icon" icon="mingcute:check-circle-fill" />
                            Successfully imported!
                        </div>
                        <button type="button" class="close-btn" data-bs-dismiss="toast">
                            <Icon class="icon" icon="mingcute:close-line" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Institute Modal -->

            <div class="modal fade primary-modal" id="instituteModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

                <div class="modal-dialog modal-dialog-centered modal-md">

                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">{{ instituteModalEdit ? 'Edit Institute' : 'Add Institute' }}</h5>
                        </div>

                        <div class="modal-body">
                            <form @submit.prevent="saveInstitute">
                                <div class="mb-3">
                                    <label class="form-label">Institute Code <span class="text-danger">*</span></label>
                                    <input v-model="instituteModalForm.code" class="form-control"
                                        placeholder="e.g., IC" />
                                    <p v-if="errors.institute_code" class="error-text mt-1">
                                        {{ errors.institute_code[0] }}
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Institute Name <span class="text-danger">*</span></label>
                                    <input v-model="instituteModalForm.name" class="form-control"
                                        placeholder="e.g., Institute of Computing" />
                                    <p v-if="errors.institute_description" class="error-text mt-1">
                                        {{ errors.institute_description[0] }}
                                    </p>
                                </div>
                            </form>
                        </div>

                        <div class="modal-footer">
                            <div class="action-buttons">
                                <button type="button" class="btn btn-sm secondary-btn"
                                    data-bs-dismiss="modal">Close</button>
                                <button class="btn btn-sm primary-btn" @click="saveInstitute" :disabled="processing">
                                    <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ instituteModalEdit ? 'Save Changes' : 'Add Institute' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Saved Added -->
            <div class="success-toast">
                <div id="instituteSavedSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-content">
                        <div class="toast-body">
                            <Icon class="icon" icon="mingcute:check-circle-fill" />
                            Institute Saved Successfully!
                        </div>
                        <button type="button" class="close-btn" data-bs-dismiss="toast">
                            <Icon class="icon" icon="mingcute:close-line" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Program Modal -->

            <div class="modal fade primary-modal" id="programModal" tabindex="-1" aria-hidden="true"
                data-bs-backdrop="static" data-bs-keyboard="false">

                <div class="modal-dialog modal-dialog-centered modal-md">

                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">{{ programModalEdit ? 'Edit Program' : 'Add Program' }}</h5>
                        </div>

                        <div class="modal-body">
                            <form @submit.prevent="saveProgram">
                                <div class="mb-3">
                                    <label class="form-label">Institute <span class="text-danger">*</span></label>

                                    <!-- When editing: show disabled text -->
                                    <input v-if="programModalEdit" type="text" class="form-control"
                                        :value="getInstituteLabel(programModalForm.institute_id)" disabled />

                                    <!-- When adding: dropdown -->
                                    <div v-else class="dropdown">
                                        <button class="btn dropdown-toggle w-100 text-start" type="button"
                                            data-bs-toggle="dropdown">
                                            <span v-if="programModalForm.institute_id">
                                                {{ getInstituteLabel(programModalForm.institute_id) }}
                                            </span>
                                            <span v-else class="text-muted">Select Institute</span>
                                        </button>
                                        <ul class="dropdown-menu w-100">
                                            <li v-for="inst in institutes" :key="inst.institute_id">
                                                <a class="dropdown-item" href="#"
                                                    @click.prevent="programModalForm.institute_id = inst.institute_id">
                                                    {{ inst.institute_code }} — {{ inst.institute_description }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <p v-if="errors.institute_id" class="error-text mt-1">
                                        {{ errors.institute_id[0] }}
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Program Code <span class="text-danger">*</span></label>
                                    <input v-model="programModalForm.code" class="form-control"
                                        placeholder="e.g., BSIT" />
                                    <p v-if="errors.program_code" class="error-text mt-1">
                                        {{ errors.program_code[0] }}
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Program Name <span class="text-danger">*</span></label>
                                    <input v-model="programModalForm.name" class="form-control"
                                        placeholder="e.g., Bachelor of Science in Information Technology" />
                                    <p v-if="errors.program_description" class="error-text mt-1">
                                        {{ errors.program_description[0] }}
                                    </p>
                                </div>
                            </form>
                        </div>

                        <div class="modal-footer">
                            <div class="action-buttons">
                                <button type="button" class="btn btn-sm secondary-btn"
                                    data-bs-dismiss="modal">Close</button>
                                <button class="btn btn-sm primary-btn" @click="saveProgram" :disabled="processing">
                                    <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ programModalEdit ? 'Save Changes' : 'Add Program' }}
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Success Program Saved -->
            <div class="success-toast">
                <div id="programSavedSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-content">
                        <div class="toast-body">
                            <Icon class="icon" icon="mingcute:check-circle-fill" />
                            Program Saved Successfully!
                        </div>
                        <button type="button" class="close-btn" data-bs-dismiss="toast">
                            <Icon class="icon" icon="mingcute:close-line" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->

            <div class="modal fade danger-modal" id="deleteConfirmModal" tabindex="-1" aria-hidden="true"
                data-bs-backdrop="static" data-bs-keyboard="false">

                <div class="modal-dialog modal-dialog-centered">

                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Delete</h5>
                        </div>

                        <div class="modal-body">
                            <p class="normal-text">{{ deleteMessage }}</p>
                        </div>

                        <div class="modal-footer">
                            <div class="action-buttons">
                                <button class="btn btn-sm primary-btn" data-bs-dismiss="modal">No</button>
                                <button class="btn btn-sm secondary-btn" @click="performDelete">Yes, Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Delete -->
            <div class="success-toast">
                <div id="deletedSuccess" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-content">
                        <div class="toast-body">
                            <Icon class="icon" icon="mingcute:check-circle-fill" />
                            Deleted Successfully!
                        </div>
                        <button type="button" class="close-btn" data-bs-dismiss="toast">
                            <Icon class="icon" icon="mingcute:close-line" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Failed Delete -->
            <div class="failed-toast">
                <div id="deleteError" class="toast" role="alert" aria-live="assertive" aria-atomic="true"
                    data-bs-autohide="true">
                    <div class="toast-content">
                        <div class="toast-body">
                            <Icon class="icon" icon="mingcute:warning-fill" />
                            Cannot delete institute because it has programs.
                        </div>
                        <button type="button" class="close-btn" data-bs-dismiss="toast">
                            <Icon class="icon" icon="mingcute:close-line" />
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AdminAppLayout>
</template>

<script>
import AdminAppLayout from '../../Layouts/AdminAppLayout.vue';
import axios from 'axios';

export default {
    name: "ManageInstituteProgram",
    components: { AdminAppLayout },
    data() {
        return {
            loading: true,

            aiCompileText: '',
            aiCompiling: false,
            aiSaving: false,
            aiCompiledData: null,
            aiError: '',

            institutes: [],

            // Institute modal
            instituteModalForm: { id: '', code: '', name: '' },
            instituteModalEdit: false,

            // Program modal
            programModalForm: { id: '', institute_id: '', code: '', name: '' },
            programModalEdit: false,

            // Delete confirmation
            deleteId: null,
            deleteType: '',
            deleteMessage: '',

            errors: {},
            processing: false,

            aiCompileTutorial:
                `
            <div>
                <div class="mb-3">
                    <strong>1. Basic Format</strong>
                    <p class="mb-1 small text-muted">Just type institute name, then list programs below it:</p>

                    <pre class="bg-light p-2 rounded text-dark small" style="font-family: 'Courier New', monospace;">
Institute of Computing
Bachelor of Science in Information Technology
Bachelor of Science in Computer Science
Bachelor of Science in Information Systems
                    </pre>
                </div>

                <div class="mb-3">
                    
                    <strong>2. Multiple Institutes</strong>
                    <p class="mb-1 small text-muted">Leave a blank line:</p>

                    <pre class="bg-light p-2 rounded text-dark small" style="font-family: 'Courier New', monospace;">
College of Engineering
BS Civil Engineering
BS Mechanical Engineering
BS Electrical Engineering

College of Business
Bachelor of Science in Accountancy
Bachelor of Science in Management Accounting
                    </pre>
                </div>

                <div class="mb-3">
                    <strong>3. Force Your Own Code (Best!)</strong>
                    <p class="mb-1 small text-muted">Add <code>(CODE)</code> at the end of any line:</p>

                    <pre class="bg-light p-2 rounded text-dark small" style="font-family: 'Courier New', monospace;">
Institute of Teacher Education
Bachelor of Secondary Education major in Mathematics (BSEd Math)
Bachelor of Secondary Education major in English (BSEd English)
Bachelor of Elementary Education (BEEd)
Master of Arts in Education (MAEd)
Doctor of Philosophy in Education (PhD Ed)
                    </pre>
                </div>

            </div>
            `
        };
    },
    computed: {
        totalToCreate() {
            if (!this.aiCompiledData || !Array.isArray(this.aiCompiledData)) return 0;
            return this.aiCompiledData.reduce((sum, item) => {
                return sum + 1 + item.programs.length;
            }, 0);
        }
    },
    mounted() {
        this.loadInstitutesProgram();

        // Activate Bootstrap Popover
        const modal = document.getElementById('aiCompileModal');
        modal.addEventListener('shown.bs.modal', () => {
            const btn = modal.querySelector('[data-bs-toggle="popover"]');
            if (btn && !btn._popover) {
                new bootstrap.Popover(btn);
            }
        });

        // Customize Bootstrap Popover
        const popoverTrigger = document.getElementById('aiCompilePopover');

        if (popoverTrigger) {
            const popover = new bootstrap.Popover(popoverTrigger, {
                html: true,
                content: this.aiCompileTutorial,
                placement: 'left',
                container: 'body',
                trigger: 'click',
                customClass: 'ai-compile-tutorial-popover'
            });

            // Close when clicking outside popover 
            document.addEventListener('click', (e) => {
                const popoverEl = document.querySelector('.popover');

                if (
                    popoverEl &&
                    !popoverTrigger.contains(e.target) &&
                    !popoverEl.contains(e.target)
                ) {
                    popover.hide();
                }
            });
        }
    },
    methods: {

        goBack() {
            window.history.back();
        },

        async loadInstitutesProgram() {
            this.loading = true;
            try {
                const res = await axios.get('/api/institutes');
                this.institutes = res.data;

                for (const inst of this.institutes) {
                    await this.loadPrograms(inst.institute_code);
                }
            } catch (error) {
                console.error(error);
                alert('Failed to load institutes & programs');
            } finally {
                this.loading = false;
            }
        },

        async loadPrograms(code) {
            const res = await axios.get(`/api/programs/${code}`);
            const inst = this.institutes.find(i => i.institute_code === code);
            if (inst) inst.programs = res.data;
        },

        getInstituteLabel(id) {
            const inst = this.institutes.find(i => i.institute_id === id);
            return inst ? `${inst.institute_code} — ${inst.institute_description}` : 'Select Institute';
        },

        openAddInstituteModal() {
            this.instituteModalEdit = false;
            this.instituteModalForm = { id: '', code: '', name: '' };
            this.errors = {};
            new bootstrap.Modal(document.getElementById('instituteModal')).show();
        },
        openEditInstitute(inst) {
            this.instituteModalEdit = true;
            this.instituteModalForm = {
                id: inst.institute_id,
                code: inst.institute_code,
                name: inst.institute_description
            };
            this.errors = {};
            new bootstrap.Modal(document.getElementById('instituteModal')).show();
        },

        async saveInstitute() {
            this.processing = true;
            this.errors = {};

            try {
                const payload = {
                    institute_code: this.instituteModalForm.code,
                    institute_description: this.instituteModalForm.name
                };

                if (this.instituteModalEdit) {
                    await axios.put(`/admin/institute/${this.instituteModalForm.id}`, payload);
                } else {
                    await axios.post('/admin/institute', payload);
                }

                // Show success toast
                this.showSuccessToast('instituteSavedSuccess');

                // Reload data
                this.loadInstitutesProgram();

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('instituteModal')).hide();

            } catch (e) {
                if (e.response?.status === 422) {
                    this.errors = e.response.data.errors || {};
                } else {
                    alert(e.response?.data?.message || 'Something went wrong.');
                }
            } finally {
                this.processing = false;
            }
        },

        openAddProgramModal() {
            this.programModalEdit = false;
            this.programModalForm = { id: '', institute_id: '', code: '', name: '' };
            this.errors = {};
            new bootstrap.Modal(document.getElementById('programModal')).show();
        },
        openEditProgram(prog) {
            this.programModalEdit = true;
            this.programModalForm = {
                id: prog.program_id,
                institute_id: prog.institute_id,
                code: prog.program_code,
                name: prog.program_description
            };
            this.errors = {};
            new bootstrap.Modal(document.getElementById('programModal')).show();
        },

        async saveProgram() {
            this.processing = true;
            this.errors = {};

            try {
                const payload = {
                    institute_id: this.programModalForm.institute_id,
                    program_code: this.programModalForm.code,
                    program_description: this.programModalForm.name
                };

                if (this.programModalEdit) {
                    await axios.put(`/admin/program/${this.programModalForm.id}`, payload);
                } else {
                    await axios.post('/admin/program', payload);
                }

                // Show success toast
                this.showSuccessToast('programSavedSuccess');

                // Reload data
                this.loadInstitutesProgram();

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('programModal')).hide();

            } catch (e) {
                if (e.response?.status === 422) {
                    this.errors = e.response.data.errors || {};
                } else {
                    alert(e.response?.data?.message || 'Something went wrong.');
                }
            } finally {
                this.processing = false;
            }
        },

        openAICompileModal() {
            this.aiCompileText = '';
            this.aiCompiledData = null;
            this.aiError = '';
            this.errors = {};
            new bootstrap.Modal(document.getElementById('aiCompileModal')).show();
        },

        async compileWithAI() {
            this.aiCompiling = true;
            this.aiError = '';
            this.aiCompiledData = null;

            try {
                const res = await axios.post('/admin/institute-program/extract-ai', {
                    text: this.aiCompileText
                });

                this.aiCompiledData = res.data;
            } catch (e) {
                this.aiError = e.response?.data?.message || 'Failed to compile. Please check your text format.';
            } finally {
                this.aiCompiling = false;
            }
        },

        async saveAICompiled() {
            if (!this.aiCompiledData) return;

            this.aiSaving = true;
            try {
                await axios.post('/admin/institute-program/import-ai', {
                    data: this.aiCompiledData
                });

                // Show success toast
                this.showSuccessToast('aiCompiledSuccess');

                // Reload data
                this.loadInstitutesProgram();

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('aiCompileModal')).hide();

            } catch (e) {
                this.aiError = e.response?.data?.message || 'Failed to save.';
            } finally {
                this.aiSaving = false;
            }
        },

        confirmDeleteInstitute(id) {
            this.deleteId = id;
            this.deleteType = 'institute';
            this.deleteMessage = 'Are you sure you want to delete this institute?';
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        },

        confirmDeleteProgram(id) {
            this.deleteId = id;
            this.deleteType = 'program';
            this.deleteMessage = 'Are you sure you want to delete this program?';
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        },

        async performDelete() {
            try {
                if (this.deleteType === 'institute') {
                    await axios.delete(`/admin/institute/${this.deleteId}`);
                    // Show success toast
                    this.showSuccessToast('deletedSuccess');

                } else if (this.deleteType === 'program') {
                    await axios.delete(`/admin/program/${this.deleteId}`);
                    // Show success toast
                    this.showSuccessToast('deletedSuccess');
                }

                // Reload data
                this.loadInstitutesProgram();

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();

            } catch (e) {
                const message = e.response?.data?.message || e.message;

                if (message === 'Cannot delete institute because it has programs.') {
                    this.showErrorToast('deleteError');

                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
                } else {
                    console.error(message);
                }

            } finally {
                this.deleteId = null;
                this.deleteType = '';
                this.deleteMessage = '';
            }
        },

        // HELPERS

        showSuccessToast(id) {
            const toastEl = document.getElementById(id);

            // Dispose any existing instance on this element
            const oldToast = bootstrap.Toast.getInstance(toastEl);
            if (oldToast) oldToast.dispose();

            // Hide any other visible success-toast
            const visibleToasts = document.querySelectorAll('.success-toast .toast.show');
            visibleToasts.forEach(t => {
                const instance = bootstrap.Toast.getInstance(t);
                if (instance) instance.hide();
            });

            // Create new toast instance and show it
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 3000
            });
            toast.show();
        },

        showErrorToast(id) {
            const toastEl = document.getElementById(id);

            // Dispose any existing instance on this element
            const oldToast = bootstrap.Toast.getInstance(toastEl);
            if (oldToast) oldToast.dispose();

            // Hide any other visible failed-toast
            const visibleToasts = document.querySelectorAll('.failed-toast .toast.show');
            visibleToasts.forEach(t => {
                const instance = bootstrap.Toast.getInstance(t);
                if (instance) instance.hide();
            });

            // Create new toast instance and show it
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 8000
            });
            toast.show();
        }
    }
};
</script>

<style scoped>
.layout {
    font-family: 'Montserrat', sans-serif;
    background-color: #f2f3f7 !important;
    display: flex;
    flex-direction: column;
    gap: 12px;
    color: #333;
}

.layout .header {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 18px;
}

.layout .header .title-container {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
}

.layout .header .title-container .back-btn {
    text-decoration: none;
    color: #333;
    cursor: pointer;
}

.layout .header .title-container .back-btn:hover {
    color: #555;
}

.layout .header .title-container .back-btn .icon {
    width: 28px;
    height: 28px;
}

.add-new-institute-program-dropdown>.btn {
    border: 2px solid #48c441;
    color: #333;
    font-weight: 500;
    border-radius: 12px;
}

.add-new-institute-program-dropdown>.btn:hover,
.add-new-institute-program-dropdown>.btn:active {
    background-color: #3ca237;
    border: solid 2px #3ca237;
    color: #f2f3f7;
}

.add-new-institute-program-dropdown .dropdown-menu {
    background-color: #ffffff;
    outline: none;
    padding: 8px 8px 4px 8px;
    border-radius: 12px;
    border: 1px solid #48c441;
}

.add-new-institute-program-dropdown .dropdown-menu .dropdown-item {
    font-weight: 500;
    border-radius: 8px;
    margin-bottom: 4px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 24px;
}

.add-new-institute-program-dropdown .dropdown-menu .dropdown-item:hover,
.add-new-institute-program-dropdown .dropdown-menu .dropdown-item.special-item:hover {
    background-color: #3ca237;
    color: #f2f3f7;
}

.add-new-institute-program-dropdown .dropdown-menu .dropdown-item.special-item {
    background-color: #48c441;
}

.institute-program-card {
    padding: 0 !important;
    border-radius: 12px !important;
    border: none !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    background: #ffffff;
}

.institute-program-card .card-body {
    display: flex;
    flex-direction: column;
    gap: 32px;
    padding: 24px;
}

.institute {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.institute-title {
    color: #2b7a29;
    font-weight: 600;
    font-size: 1rem;
    margin: 0;
}

.institute-actions {
    display: flex;
    gap: 8px;
}

.institute-actions .btn {
    border-radius: 12px;
    padding: 6px 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid #48c441;
    background-color: #48c441;
    color: #fff;
    transition: all 0.2s ease;
}

.institute-actions .btn:hover {
    background-color: #3ca237;
    border-color: #3ca237;
}

.program {
    padding-left: 0;
    margin-top: 16px;
}

.program-title {
    color: #333;
    margin: 0;
    font-size: 0.95rem;
}

.program .list-group-item {
    border: none !important;
    padding: 14px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    transition: background 0.15s ease;
}

.program .list-group-item:hover {
    background: #fafafa;
}

.program-actions {
    display: flex;
    gap: 8px;
}

.program-actions .btn {
    border-radius: 12px;
    padding: 6px 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid #48c441;
    background-color: transparent;
    color: #333;
    transition: all 0.2s ease;
}

.program-actions .btn:hover {
    background-color: #3ca237;
    border-color: #3ca237;
    color: #fff;
}

.program .list-group-item.text-muted {
    text-align: left;
    padding: 12px 0;
    color: #777;
    background: none;
}
</style>

<style>
.ai-compile-tutorial-popover {
    max-width: 380px !important;
    width: 380px !important;
    border-radius: 12px !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
}

.ai-compile-tutorial-popover .popover-body {
    max-height: 380px;
    overflow-y: auto;
    padding: 1rem;
}
</style>
