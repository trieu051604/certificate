<template>
    <div class="registration-fields">
        <div class="registration-field" v-for="field in fields" :key="field.key">
            <label :for="prefix + field.key">{{ field.label }}<span v-if="field.required" class="required-mark"> *</span></label>
            <select v-if="field.type === 'select' || field.type === 'province'" :id="prefix + field.key" :value="value[field.key] || ''" :required="!preview && field.required" @change="update(field.key, $event.target.value)">
                <option value="">Chọn {{ field.label.toLowerCase() }}</option>
                <template v-if="field.type === 'province'"><option v-for="province in provinces" :key="province.id" :value="province.id">{{ province.province }}</option></template>
                <template v-else><option v-for="option in field.options" :key="option" :value="option">{{ option }}</option></template>
            </select>
            <textarea v-else-if="field.type === 'textarea'" :id="prefix + field.key" :value="value[field.key] || ''" :placeholder="field.placeholder" :required="!preview && field.required" maxlength="2000" rows="3" @input="update(field.key, $event.target.value)"></textarea>
            <input v-else :id="prefix + field.key" :type="field.type" :value="value[field.key] || ''" :placeholder="field.placeholder" :required="!preview && field.required" :maxlength="field.key.startsWith('custom_') ? 2000 : 255" :pattern="field.type === 'tel' ? '[0-9]{10,11}' : null" step="any" @input="update(field.key, $event.target.value)">
        </div>
    </div>
</template>
<script>
export default {
    props: { fields: { type: Array, default: () => [] }, value: { type: Object, default: () => ({}) }, provinces: { type: Array, default: () => [] }, preview: Boolean, prefix: { type: String, default: 'registration-' } },
    methods: { update(key, value) { this.$emit('input', { ...this.value, [key]: value }); } }
};
</script>
<style scoped>
.registration-fields { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 22px 36px; width:100%; margin-bottom:24px; }
.registration-field label { display:block; margin-bottom:6px; font-weight:500; }
.registration-field input, .registration-field select, .registration-field textarea { width:100%; border:1px solid #9ca3af; border-radius:4px; padding:9px 12px; background:white; color:#222; min-height:40px; }
.registration-field input:focus, .registration-field select:focus, .registration-field textarea:focus { outline:2px solid #ff721d; outline-offset:1px; }
.required-mark { color:#d33817; }
@media(max-width:767px) { .registration-fields { grid-template-columns:1fr; } }
</style>
