<template>
    <modal @close="$emit('close')" class="document-recall-confirmation">            
        <template slot="header">
            <p slot="header" class="modal-card-title">Document Recall</p>
        </template>

        <template>
            <div class="columns manage-recall">
                <div class="table-wrapper">
                    <div class="table-container">
                        <div class="table-body-wrapper">
                            <table id="table_recall">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Document Name <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th>
                                        <th>Created At <i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></th>
                                        <th class="table-action">Edit</th>
                                        <th class="table-action">Delete</th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="table-action-bottom">
                        <div class="table-pages">
                            <span class="pages-current">{{ currentPage }}</span>/<span class="pages-total">{{ totalPages
                            }}</span>
                            <span id="prev-page" class="prev-page"><i class="fa fa-chevron-left"></i></span>
                            <span id="next-page" class="next-page"><i class="fa fa-chevron-right"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            
        </template>

        <template slot="footer">
        </template>
        <transition name="fade" mode="out-in">
            <modal-delete-document v-if="modals.delete" :show="modals.delete" :document="modals.deleteId" @close="modals.delete = false" @delete="finishedDelete"></modal-delete-document>
        </transition>
    </modal>

</template>

<script>
try {
    window.$ = window.jQuery = require('jquery');
} catch (e) { }
require('datatables.net');

import Modal from './Modal.vue';
import ModalDeleteDocument from './Modal-delete-document.vue';

export default {
    data() {
        return {
            refresh: () => { },
            changeUrl: () => { },
            currentPage: 1,
            modals: {
                create: false,
                delete: false,
                deleteId: undefined,
                edit: false,
                editUser: undefined,
            },
            selected: [],
            totalPages: 1,
            dropdown: false,
            url: '/admin/manage-users/data',
            urlUsers: '/admin/manage-users/data',
            urlAdmins: '/admin/manage-admins/data',
            admin: false,
            width: 0,
            coverContent: { 
                projectType: this.$store.state.dashboard.modals.addCover.content.projectType,
                project:this.$store.state.dashboard.modals.addCover.content.project, 
                customer: this.$store.state.dashboard.modals.addCover.content.customer, 
                job: this.$store.state.dashboard.modals.addCover.content.job
            }
        };
    },
    computed: {

    },
    components: {
        Modal,
        ModalDeleteDocument,
    },
    methods: {
        setRefresh(callback) {
            this.refresh = callback;
        },
        setUrl(callback) {
            this.changeUrl = callback;
        },
        deselectAll() {
            this.selected = [];
        },
        select(id) {
            this.selected.push(id);
        },
        isSelected(id) {
            return this.selected.indexOf(parseInt(id)) > -1;
        },
        deselect(id) {
            this.selected.splice(this.selected.indexOf(id), 1);
        },
        setCurrentPage(page) {
            this.currentPage = page;
        },
        setTotalPages(page) {
            this.totalPages = page;
        },
        openDelete(id) {
            if (id) {
                this.modals.deleteId = id;
                return this.modals.delete = true;
            }

            if (this.selected.length) {
                return this.modals.delete = true;
            }
        },
        finishedDelete() {
            this.refresh();
            this.modals.deleteId = undefined;
            this.modals.delete = false;
        },
        updateModalsSave(modalName, data) {
            if(data.length !== 0) {
                this.$store.state.dashboard.modals[modalName].content = data;
                this.$store.state.dashboard.modals[modalName].show = false;
                this.$store.state.dashboard.modals[modalName].exists = true;
                this.$store.state.successState = false;
                this.$emit('close');
            }
        },
        openEdit(document) {
            
            if (document) {
                var document_history = JSON.parse(document.document_history.replace(/'/g, '"').replace(/null/g, '""'));
                console.log("selectedFiles", this.$store.state.dashboard.selectedFiles);
                console.log("docselectedFiles", document_history.items);
                this.$store.state.dashboard.selectedFiles = document_history.items;
                this.updateModalsSave('addHeader', document_history.header);
                this.updateModalsSave('addFooter', document_history.footer);
                this.updateModalsSave('addCover', document_history.cover);
                this.updateModalsSave('addOperation', document_history.operation);
                this.$store.state.dashboard.recallState = true;
                var document_path =  document.document_path.replace('public', 'storage');
                this.$store.state.dashboard.oldDocumentLink = document_path;
                this.$emit('close');
            }
        },

        finishedEdit() {
            this.refresh();
            this.modals.editUser = undefined;
            this.modals.edit = false;
        },
        changeAdmin() {
            this.changeUrl(this.urlAdmins);
            this.toggleDropdown();
            this.admin = true;
        },
        changeUser() {
            this.changeUrl(this.urlUsers);
            this.toggleDropdown();
            this.admin = false;
        },
        navbarOutside(ev) {
            window.removeEventListener('click', this.navbarOutside);
            this.dropdown = false;
        },
        toggleDropdown() {
            if (this.dropdown) {
                window.removeEventListener('click', this.navbarOutside);
                this.dropdown = false;
            } else {
                window.addEventListener('click', this.navbarOutside);
                this.dropdown = true;
            }
        },
    },
    mounted() {
        $(() => {
            var that = this;
            const dt = $('#table_recall').DataTable({
                processing: true,
                language: {
                    processing: "Loading data..."
                },
                serverSide: true,
                pageLength: 20,
                ajax: '/dashboard/manage-recall/data',
                order: [[2, "desc"]],
                bScrollCollapse: true,
                columns: [
                    {
                        data: null,
                        defaultContent: '',
                        searchable: false,
                        "bSearchable": false,
                        "orderable": false,
                    },
                    {
                        data: 'name',
                        render: function (data, type, full) {
                            return '<label for="user-' + full.id + '"></label>' + data;
                        }
                    },
                    {
                        data: 'created_at',
                    },
                    {
                        data: null,
                        className: 'table-action',
                        searchable: false,
                        "bSearchable": false,
                        "orderable": false,
                        render: function (data, type, full) {
                            console.log(full.document_history.replace(/&quot;/g, '"'));
                            return '<i class="fa fa-pencil-square-o edit-user" aria-hidden="true" data-id="' + full.id + '" data-name="' + full.name + '" data-path="' + full.path + '"  data-history="'+full.document_history.replace(/&quot;/g, "'")+'"></i>';
                        },
                    },
                    {
                        data: null,
                        className: 'table-action',
                        searchable: false,
                        "bSearchable": false,
                        "orderable": false,
                        render: function (data, type, full) {
                            return '<i class="fa fa-trash-o delete-user" aria-hidden="true" data-id="' + full.id + '"></i>';
                        },
                    },
                    {
                        data: null,
                        defaultContent: '',
                        searchable: false,
                        "bSearchable": false,
                        "orderable": false,
                    }
                ]
            });

            that.setRefresh(function () {
                dt.draw();
            });;

            that.setUrl(function (url) {
                dt.ajax.url(url).load();
            });

            $(document).on('change', '.table-checkbox', function () {
                const id = $(this).data('id');
                const val = $(this).prop('checked');

                if (val) {

                    $(this).parent().parent().addClass('checked');
                    that.select(id);

                } else {

                    $(this).parent().parent().removeClass('checked');
                    that.deselect(id);
                }

            });

            $(document).on('click', '#deselect-all', function () {
                that.deselectAll();
                $('.table-checkbox').prop('checked', false);
                $('.checked').removeClass('checked');
            });

            $(document).on('keyup', '#users-search', function () {
                dt.search($(this).val()).draw();
            });

            $(document).on('click', '#prev-page', function () {
                dt.page('previous').draw('page');
            });

            $(document).on('click', '#next-page', function () {
                dt.page('next').draw('page');
            });

            $(document).on('click', '.edit-user', function () {
                that.openEdit(
                    {
                        id: $(this).data('id'),
                        name: $(this).data('name'),
                        document_history: $(this).data('history'),
                        document_path: $(this).data('path'),
                    }
                );
            });

            $(document).on('click', '.delete-user', function () {
                that.openDelete($(this).data('id'));
            });

            dt.on('draw.dt', function () {

                that.setCurrentPage(dt.page.info().page + 1);
                that.setTotalPages(dt.page.info().pages);

                $('.table-checkbox').each(function () {
                    if ($(this).prop('checked')) {
                        $(this).parent().parent().addClass('checked');
                    }
                });

            });

        });
    }
}

</script>

<style lang="scss">
@import "resources/assets/sass/variables";
@import "resources/assets/sass/mixins";
.document-recall-confirmation .modal-card {width: 80% !important;}
.manage-recall .table-wrapper {
    padding: 0px;
    background: white;
}
table {
    border-collapse: collapse;
    border-spacing: 0;
    margin-bottom: 20px;
    margin-top: 30px;
}
#table_recall_filter input {
    flex-grow: 1;
    font-family: "Raleway";
    font-size: 16px;
    font-weight: 500;
    height: 50px;
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #d4d5d6;
    box-shadow: 0 3px 5px -1px rgba(0, 0, 0, 0.04), 0 4px 8px rgba(0, 0, 0, 0.03), 0 1px 16px rgba(0, 0, 0, 0.02);
    border-radius: 8px;
    color: #404040;
    padding-left: 25px;
    transition: box-shadow 0.1s ease-in-out, border-color 0.1s ease-in-out;
}
.manage-recall .table-wrapper .table-container {
    border: 0px;
    border-radius: 0px;
    box-shadow: unset;
    .dataTables_info, .dataTables_paginate {
        display: none;
    }
}
.manage-recall .table-wrapper .table-action-bottom {
    display: flex;
    justify-content: end;
    align-items: center;
}
.manage-recall .table-wrapper .table-action-bottom .table-pages {
    display: flex;
    align-items: center;
}
.manage-recall .table-wrapper .table-action-bottom .table-pages {
    .prev-page, .next-page{
        margin: 0px 10px;
        box-shadow: unset;
    }
    .prev-page:hover, .next-page:hover {
        box-shadow: unset;
    }
}
.manage-recall .table-wrapper .table-container #table_recall_wrapper #table_recall tbody tr:last-child {
    border-bottom: 1px solid #f2f2f2;
}
.manage-recall .table-wrapper .table-container #table_recall_wrapper #table_recall thead {
    border-top: 1px solid #f2f2f2;
}
.manage-recall .table-wrapper .table-container #table_recall_wrapper #table_recall tbody tr td:last-child, 
.manage-recall .table-wrapper .table-container #table_recall_wrapper #table_recall thead tr th:last-child {
    border-right: 1px solid #f2f2f2;;
}
#table_recall_processing {
    display: none !important;
}
.dataTables_length {
    display: none;
}
.manage-recall .table-body-wrapper {
    overflow-x: auto;
}
</style>

