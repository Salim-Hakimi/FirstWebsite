<script setup>
import { computed, reactive, ref, watch } from 'vue';
import BaseModal from '../common/BaseModal.vue';
import { api } from '../../services/api';

const props = defineProps({
    open: Boolean,
    userId: {
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
    roles: {},
    statuses: {},
});
const form = reactive({
    name: '',
    email: '',
    phone: '',
    role: '',
    status: '',
    password: '',
    password_confirmation: '',
    profile_photo: null,
    profile_photo_url: null,
    remove_profile_photo: false,
});

const isEditing = computed(() => Boolean(props.userId));
const title = computed(() => (isEditing.value ? 'ویرایش کاربر' : 'ساخت کاربر'));
const canSubmit = computed(() => {
    const requiredFilled = form.name.trim()
        && form.email.trim()
        && form.phone.trim()
        && form.role
        && form.status;

    if (!requiredFilled) {
        return false;
    }

    if (!isEditing.value) {
        return Boolean(form.password && form.password_confirmation);
    }

    return !form.password || Boolean(form.password_confirmation);
});

function resetForm() {
    message.value = '';
    errors.value = {};
    form.name = '';
    form.email = '';
    form.phone = '';
    form.role = '';
    form.status = '';
    form.password = '';
    form.password_confirmation = '';
    form.profile_photo = null;
    form.profile_photo_url = null;
    form.remove_profile_photo = false;
}

function fieldError(field) {
    return errors.value?.[field]?.[0] || '';
}

function setOptions(payload) {
    options.roles = payload.roles || {};
    options.statuses = payload.statuses || {};
}

function fillForm(payload) {
    form.name = payload.name || '';
    form.email = payload.email || '';
    form.phone = payload.phone || '';
    form.role = payload.role || '';
    form.status = payload.status || '';
    form.profile_photo_url = payload.profile_photo_url || null;
}

async function loadForm() {
    resetForm();
    loading.value = true;

    try {
        if (isEditing.value) {
            const response = await api.get(`/api/admin/users/${props.userId}`);
            setOptions(response.data.options || {});
            fillForm(response.data.form || {});
            return;
        }

        const response = await api.get('/api/admin/users/options');
        setOptions(response.data || {});
        form.role = response.data.defaults?.role || Object.keys(options.roles)[0] || '';
        form.status = response.data.defaults?.status || Object.keys(options.statuses)[0] || '';
    } catch (error) {
        message.value = error.message || 'بارگذاری فرم ناموفق بود.';
    } finally {
        loading.value = false;
    }
}

function onFileChange(event) {
    form.profile_photo = event.target.files?.[0] || null;
}

function buildPayload() {
    const payload = new FormData();
    payload.append('name', form.name);
    payload.append('email', form.email);
    payload.append('phone', form.phone);
    payload.append('role', form.role);
    payload.append('status', form.status);
    payload.append('password', form.password);
    payload.append('password_confirmation', form.password_confirmation);

    if (form.profile_photo) {
        payload.append('profile_photo', form.profile_photo);
    }

    if (form.remove_profile_photo) {
        payload.append('remove_profile_photo', '1');
    }

    return payload;
}

async function submit() {
    if (!canSubmit.value || saving.value) {
        return;
    }

    saving.value = true;
    message.value = '';
    errors.value = {};

    try {
        const url = isEditing.value ? `/api/admin/users/${props.userId}` : '/api/admin/users';
        const response = await api.post(url, buildPayload());
        emit('saved', response.data);
        emit('close');
    } catch (error) {
        errors.value = error.errors || {};
        message.value = error.message || 'ذخیره کاربر ناموفق بود.';
    } finally {
        saving.value = false;
    }
}

watch(
    () => [props.open, props.userId],
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
                        <span>نام کامل</span>
                        <input v-model="form.name" required>
                        <small v-if="fieldError('name')" class="fanous-vue-field__error">{{ fieldError('name') }}</small>
                    </label>

                    <label class="fanous-vue-field">
                        <span>ایمیل</span>
                        <input v-model="form.email" type="email" required>
                        <small v-if="fieldError('email')" class="fanous-vue-field__error">{{ fieldError('email') }}</small>
                    </label>

                    <label class="fanous-vue-field">
                        <span>واتساپ</span>
                        <input v-model="form.phone" required>
                        <small v-if="fieldError('phone')" class="fanous-vue-field__error">{{ fieldError('phone') }}</small>
                    </label>

                    <label class="fanous-vue-field">
                        <span>نقش</span>
                        <select v-model="form.role" required>
                            <option v-for="(label, value) in options.roles" :key="value" :value="value">{{ label }}</option>
                        </select>
                        <small v-if="fieldError('role')" class="fanous-vue-field__error">{{ fieldError('role') }}</small>
                    </label>

                    <label class="fanous-vue-field">
                        <span>وضعیت</span>
                        <select v-model="form.status" required>
                            <option v-for="(label, value) in options.statuses" :key="value" :value="value">{{ label }}</option>
                        </select>
                        <small v-if="fieldError('status')" class="fanous-vue-field__error">{{ fieldError('status') }}</small>
                    </label>

                    <label class="fanous-vue-field">
                        <span>{{ isEditing ? 'رمز عبور جدید' : 'رمز عبور' }}</span>
                        <input v-model="form.password" type="password" :required="!isEditing" autocomplete="new-password">
                        <small>رمز باید حروف بزرگ، کوچک، عدد و سمبول داشته باشد.</small>
                        <small v-if="fieldError('password')" class="fanous-vue-field__error">{{ fieldError('password') }}</small>
                    </label>

                    <label class="fanous-vue-field">
                        <span>تکرار رمز عبور</span>
                        <input v-model="form.password_confirmation" type="password" :required="!isEditing || Boolean(form.password)" autocomplete="new-password">
                    </label>

                    <label class="fanous-vue-field fanous-vue-form-grid__full">
                        <span>عکس پروفایل</span>
                        <input type="file" accept="image/*" @change="onFileChange">
                        <small v-if="fieldError('profile_photo')" class="fanous-vue-field__error">{{ fieldError('profile_photo') }}</small>
                    </label>

                    <label v-if="form.profile_photo_url" class="fanous-vue-checkbox fanous-vue-form-grid__full">
                        <input v-model="form.remove_profile_photo" type="checkbox">
                        <span>حذف عکس فعلی</span>
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
