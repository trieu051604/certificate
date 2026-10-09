<template>
    <div class="row">
        <input type="hidden" name="fields_json" :value="JSON.stringify(fields)">
        <div class="col-xl-6">
            <p>Họ tên và email dùng để cấp chứng chỉ nên luôn bắt buộc. Các trường khác có thể thêm, xóa và sắp xếp.</p>
            <div class="card" v-for="(field, index) in fields" :key="field.key">
                <div class="card-header d-flex align-items-center">
                    <strong class="mr-auto">{{ index + 1 }}. {{ field.label || 'Trường mới' }}</strong>
                    <button type="button" class="btn btn-sm btn-light" :disabled="index === 0" @click="move(index,-1)" aria-label="Di chuyển lên">↑</button>
                    <button type="button" class="btn btn-sm btn-light" :disabled="index === fields.length - 1" @click="move(index,1)" aria-label="Di chuyển xuống">↓</button>
                    <button type="button" class="btn btn-sm btn-outline-danger ml-2" :disabled="core(field)" @click="fields.splice(index,1)">Xóa</button>
                </div>
                <div class="card-body">
                    <label :for="'label-' + field.key">Nhãn hiển thị</label><input :id="'label-' + field.key" class="form-control mb-2" v-model="field.label" required maxlength="150">
                    <label :for="'type-' + field.key">Kiểu nhập</label><select :id="'type-' + field.key" class="form-control mb-2" v-model="field.type" :disabled="!field.key.startsWith('custom_')">
                        <option v-for="(label,type) in typesFor(field)" :key="type" :value="type">{{ label }}</option>
                    </select>
                    <label :for="'placeholder-' + field.key">Gợi ý nhập</label><input :id="'placeholder-' + field.key" class="form-control mb-2" v-model="field.placeholder" maxlength="255">
                    <div v-if="field.type === 'select'"><label :for="'options-' + field.key">Các lựa chọn (mỗi dòng một lựa chọn)</label><textarea :id="'options-' + field.key" class="form-control mb-2" :value="field.options.join('\n')" @input="field.options = $event.target.value.split('\n')" rows="4" required></textarea></div>
                    <label><input type="checkbox" v-model="field.required" :disabled="core(field)"> Bắt buộc nhập</label>
                </div>
            </div>
            <div class="card card-body">
                <label for="new-field">Thêm thông tin cần thu thập</label>
                <select id="new-field" class="form-control mb-2" v-model="newKey">
                    <option value="custom">Thông tin tùy chỉnh</option>
                    <option v-for="(item,key) in available" :key="key" :value="key">{{ item.label }}</option>
                </select>
                <button type="button" class="btn btn-outline-primary" :disabled="fields.length >= 30" @click="add">+ Thêm trường</button>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card preview-card"><div class="card-header"><strong>Xem trước form đăng ký</strong></div><div class="card-body">
                <p class="text-muted">Bạn có thể nhập thử để xem cách form hoạt động.</p>
                <registration-fields :fields="fields" :provinces="provinceList" v-model="previewValues" preview prefix="preview-"></registration-fields>
                <div class="text-muted">Câu hỏi bài thi sẽ hiển thị bên dưới form này.</div>
            </div></div>
        </div>
    </div>
</template>
<script>
import RegistrationFields from './RegistrationFields.vue';
export default {
    components: { RegistrationFields },
    props: { schema: String, provinces: String },
    data() {
        let fields; try { fields = JSON.parse(this.schema); } catch (_) { fields = []; }
        if (!Array.isArray(fields)) fields = [];
        return {
            fields, previewValues: {}, provinceList: JSON.parse(this.provinces || '[]'), newKey:'custom',
            types: { text:'Văn bản ngắn', textarea:'Văn bản nhiều dòng', email:'Email', tel:'Số điện thoại', date:'Ngày', number:'Số', select:'Danh sách lựa chọn', province:'Tỉnh/Thành phố' },
            system: {
                phone:{label:'Số điện thoại', type:'tel'}, name_school:{label:'Trường THPT',type:'text'},
                province:{label:'Tỉnh/Thành phố',type:'province'}, you_is:{label:'Bạn là',type:'select',options:['Giáo viên','Phụ huynh','Học sinh','Khác']},
                major:{label:'Ngành',type:'select',options:['Chưa xác định']}
            }
        };
    },
    computed: { available() { return Object.fromEntries(Object.entries(this.system).filter(([key]) => !this.fields.some(field => field.key === key))); } },
    methods: {
        typesFor(field) { return field.key.startsWith('custom_') ? Object.fromEntries(Object.entries(this.types).filter(([type]) => type !== 'province')) : this.types; },
        core(field) { return ['fullname','email'].includes(field.key); },
        move(index,step) { const field = this.fields.splice(index,1)[0]; this.fields.splice(index+step,0,field); },
        add() {
            if(this.fields.length >= 30) return;
            let key = this.newKey, item = this.system[key];
            if(key === 'custom') { key = 'custom_' + Date.now().toString(36) + Math.random().toString(36).slice(2,7); item = {label:'Thông tin mới',type:'text'}; }
            if(!item || this.fields.some(field => field.key === key)) return;
            this.fields.push({key,...item,required:false,placeholder:'',options:[...(item.options || [])]}); this.newKey = 'custom';
        }
    }
};
</script>
<style scoped>.preview-card { position:sticky; top:20px; border-top:3px solid #ff721d; }</style>
