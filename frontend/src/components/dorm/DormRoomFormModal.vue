<script setup>
import { computed, reactive, ref, watch } from 'vue';
import BaseModal from '../common/BaseModal.vue';
import { api } from '../../services/api';

const props = defineProps({
    open: Boolean,
    roomId: {
        type: [Number, String, null],
        default: null,
    },
});

const emit = defineEmits(['close', 'saved']);

const loading = ref(false);
const saving = ref(false);
const message = ref('');
const errors = ref({});
const options = reactive({
    statuses: {},
    capacities: [4, 6, 8],
});
const form = reactive({
    room_number: '',
    capacity: 4,
    floor: '',
    status: 'active',
    notes: '',
});

const isEditing = computed(() => Boolean(props.roomId));
const title = computed(() => (isEditing.value ? 'ویرایش اتاق' : 'ثبت اتاق'));
const canSubmit = computed(() => Boolean(form.room_number.trim() && form.capacity && form.status));

function resetForm() {
    message.value = '';
    errors.value = {};
    form.room_number = '';
    form.capacity = 4;
    form.floor = '';
    form.status = 'active';
    form.notes = '';
}

function fieldError(field) {
    return errors.value?.[field]?.[0] || '';
}

function fillForm(payload) {
    form.room_number = payload.room_number || '';
    form.capacity = Number(payload.capacity || 4);
    form.floor = payload.floor || '';
    form.status = payload.status || 'active';
    form.notes = payload.notes || '';
}

function setOptions(payload) {
    options.statuses = payload.statuses || {};
    options.capacities = payload.capacities || [4, 6, 8];
}

async function loadForm() {
    resetForm();
    loading.value = true;

    try {
        if (isEditing.value) {
            const response = await api.get(`/api/dorm/rooms/${props.roomId}`);
            setOptions(response.data.options || {});
            fillForm(response.data.form || {});
            return;
        }

        const response = await api.get('/api/dorm/rooms/options');
        setOptions(response.data || {});
        form.capacity = Number(response.data.defaults?.capacity || 4);
        form.status = response.data.defaults?.status || 'active';
    } catch (error) {
        message.value = error.message || 'بارگذاری فرم ناموفق بود.';
    } finally {
        loading.value = false;
    }
}

async function submit() {
    if (!canSubmit.value || saving.value) {
        return;
    }

    saving.value = true;
    message.value = '';
    errors.value = {};

    try {
        const url = isEditing.value ? `/api/dorm/rooms/${props.roomId}` : '/api/dorm/rooms';
        const response = await api.post(url, { ...form });
        emit('saved', response.data);
        emit('close');
    } catch (error) {
        errors.value = error.errors || {};
        message.value = error.message || 'ذخیره اتاق ناموفق بود.';
    } finally {
        saving.value = false;
    }
}

watch(
    () => [props.open, props.roomId],
    () => {
        if (props.open) {
            loadForm();
        }
    },
    { immediate: true },
);
</script>

<template>
    <BaseModal :open="open" :title="title" @close="emit('close')">
        <form class="fanous-vue-form" @submit.prevent="submit">
            <div v-if="loading" class="fanous-vue-state">در حال آماده‌سازی فرم...</div>

            <template v-else>
                <p v-if="message" class="fanous-vue-form-error">{{ message }}</p>

                <div class="fanous-vue-form-grid">
                    <label class="fanous-vue-field">
                        <span>نمبر اتاق</span>
                        <input v-model="form.room_number" required>
                        <small v-if="fieldError('room_number')" class="fanous-vue-field__error">{{ fieldError('room_number') }}</small>
                    </label>

                    <label class="fanous-vue-field">
                        <span>ظرفیت</span>
                        <select v-model.number="form.capacity" required>
                            <option v-for="capacity in options.capacities" :key="capacity" :value="capacity">
                                {{ capacity }} بستر
                            </option>
                        </select>
                        <small v-if="fieldError('capacity')" class="fanous-vue-field__error">{{ fieldError('capacity') }}</small>
                    </label>

                    <label class="fanous-vue-field">
                        <span>منزل</span>
                        <input v-model="form.floor" placeholder="مثلاً: منزل دوم">
                        <small v-if="fieldError('floor')" class="fanous-vue-field__error">{{ fieldError('floor') }}</small>
                    </label>

                    <label class="fanous-vue-field">
                        <span>وضعیت</span>
                        <select v-model="form.status" required>
                            <option v-for="(label, value) in options.statuses" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                        <small v-if="fieldError('status')" class="fanous-vue-field__error">{{ fieldError('status') }}</small>
                    </label>

                    <label class="fanous-vue-field fanous-vue-form-grid__full">
                        <span>یادداشت</span>
                        <textarea v-model="form.notes" rows="4" placeholder="وضعیت، امکانات یا یادداشت مدیریت"></textarea>
                        <small v-if="fieldError('notes')" class="fanous-vue-field__error">{{ fieldError('notes') }}</small>
                    </label>
                </div>

                <div class="fanous-vue-form-actions">
                    <button type="button" @click="emit('close')">لغو</button>
                    <button class="is-primary" type="submit" :disabled="!canSubmit || saving">
                        {{ saving ? 'در حال ذخیره...' : 'ذخیره' }}
                    </button>
                </div>
            </template>
        </form>
    </BaseModal>
</template>
