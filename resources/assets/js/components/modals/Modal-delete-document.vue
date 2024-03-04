<template>
    <modal :show="show" @close="$emit('close')" class="modal default-modal">

        <template slot="header">
            <p slot="header" class="modal-card-title">Delete <span style="text-transform: capitalize;">Document</span>?</p>
        </template>

        <template>
            <div class="field">
                <p> Are you sure? This action will permanently delete the document. </p>
            </div>
        </template>

        <template slot="footer">
            <button class="button cancel-btn" @click="$emit('close')">Cancel</button>
            <button class="button is-info" @click="request">Delete</button>
        </template>

    </modal>
</template>

<script>
import Modal from './Modal.vue';

export default {
    props: {
        show: {
            type: Boolean,
            required: true,
            default: false,
        },
        document: {
            type: Number,
            default: 0, // Set a default value of 0 for the document prop
        },
    },
    components: {
        Modal,
    },
    methods: {
        request() {
            axios.delete('/dashboard/deleteDocFile', {data: { document: [this.document] }})
            .then(() => {
                this.$emit('delete');
            })
            .catch((response) => {
                console.log(response);
            });
        },
    }
}
</script>

<style lang="scss">
    .modal {
        .modal-card {
            width: 460px;

            .modal-card-body {
                .field{
                    p:not(.help){
                        text-align: center;
                    }
                }
            }
        }

    }
</style>
