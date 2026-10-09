<template>
    <div>
        <label for="registration_form_id">Form thông tin đăng ký</label>
        <select name="registration_form_id" id="registration_form_id" class="form-control mb-2" v-model="selected" @change="values = {}">
            <option value="">Form mặc định</option><option v-for="form in formList" :key="form.id" :value="String(form.id)">{{ form.name }}</option>
        </select>
        <details class="border rounded p-3 mb-2"><summary>Xem trước form đã chọn</summary><div class="pt-3"><registration-fields :fields="fields" :provinces="provinceList" v-model="values" preview prefix="course-preview-"></registration-fields></div></details>
    </div>
</template>
<script>
import RegistrationFields from './RegistrationFields.vue';
export default {
    components:{RegistrationFields}, props:{forms:String,initial:String,defaults:String,provinces:String},
    data() { return {selected:this.initial || '', formList:JSON.parse(this.forms), defaultFields:JSON.parse(this.defaults), provinceList:JSON.parse(this.provinces),values:{}}; },
    computed:{fields(){return this.formList.find(form=>String(form.id)===this.selected)?.fields || this.defaultFields;}}
};
</script>
