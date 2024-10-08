<template>
    <modal
        @modal-close="handleClose"
        data-testid="ApiTokenCopier"
        tabindex="-1"
        role="dialogue">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden max-w-sm mx-auto">
            <div class="p-6 space-y-4">
                <p class="text-gray-700">
                    {{ data.message }}
                </p>
                <div class="flex justify-between items-center bg-gray-100 border border-gray-300 rounded-lg p-4 text-gray-900 font-mono text-sm break-all">
                    <span class="truncate" id="api-token-value">{{ data.token }}</span>
                    <div class="ml-2">
                        <font-awesome-icon :icon="['fas', 'copy']" @click="copyToken" class="cursor-pointer" />
                    </div>
                </div>
                <p v-if="copyMessage" class="text-green-500">{{ copyMessage }}</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-2">
                <button class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-red-300"
                    @click.prevent="handleClose()">Close </button>
            </div>
        </div>
    </modal>
</template>
<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faCopy } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';

library.add(faCopy);

export default {
    components: {
        FontAwesomeIcon,
    },
    props: {
        data: { type: Object, required: true },
    },
    data() {
        return {
            copyMessage: '',
        };
    },
    methods: {
        handleClose() {
            this.$emit('close');
        },
        copyToken() {
            try {
                navigator.clipboard.writeText(this.data.token);
                this.copyMessage = 'Token was copied';
            } catch (err) {
                console.error('Failed to copy: ', err);
                this.copyMessage = 'Failed to copy token';
            }
        }
    },
};
</script>

