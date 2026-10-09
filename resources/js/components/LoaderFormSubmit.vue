<template>
    <div class="row sectionBlockLayout text-start" style="margin:0;padding:8px">
        <div class="container"><div class="container__question">
            <h1>{{ courseInfo.course }}</h1>
            <form @submit.prevent="submit">
                <registration-fields :fields="courseInfo.registration_fields || []" :provinces="provinceList" v-model="registration"></registration-fields>
                <div class="registration-course"><label>Khóa học:</label><input :value="courseInfo.course" disabled></div>
                <div class="question__list">
                    <div class="quest__item" v-for="question in courseInfo.questions" :key="question.id">
                        <div class="question__title"><h3>{{ question.question }}</h3></div>
                        <div class="answer"><div class="container__answer" v-for="answer in question.answers" :key="answer.id">
                            <input type="radio" :name="'question-' + question.id" :id="'answer-' + question.id + '-' + answer.id" :value="answer.id" v-model="selectedAnswers[question.id]" required>
                            <label :for="'answer-' + question.id + '-' + answer.id">{{ answer.answer }}</label>
                        </div></div>
                    </div>
                    <button type="submit" class="btn btn-success" :disabled="isSubmit">{{ isSubmit ? 'Đang chấm điểm...' : 'Nộp bài' }}</button>
                    <div v-if="result && result.isPass && !game" class="certificate-result">
                        Bạn đã đạt {{ result.point }}/{{ courseInfo.questions.length }} điểm.
                        <a :href="result.download_url" target="_blank" rel="noopener">Tải chứng chỉ PDF</a>
                    </div>
                </div>
            </form>
        </div></div>
    </div>
</template>
<script>
import { mapGetters } from 'vuex';
import RegistrationFields from './RegistrationFields.vue';
export default {
    components: { RegistrationFields },
    props: { provinces: String, schools: String, course: String, game: Boolean },
    data() { return { courseInfo:JSON.parse(this.course), provinceList:JSON.parse(this.provinces || '[]'), registration:{}, selectedAnswers:{}, isSubmit:false, result:null }; },
    computed: { ...mapGetters({ certification:'certification' }) },
    watch: {
        certification(data) {
            if(!data) return;
            this.isSubmit = false;
            if(data.error) { this.alert(data.error,'error'); return; }
            this.result = data;
            if(data.isPass) {
                if(this.game) {
                    const message = `Chúc mừng bạn đã đúng ${data.point}/${this.courseInfo.questions.length} câu. Hãy đưa kết quả này cho Đại sứ FPTU để nhận quà nhé!`;
                    if(this.courseInfo.url_redirect) this.$store.dispatch('showAlertRedirect',{message,status:'success',url:this.courseInfo.url_redirect});
                    else this.alert(message,'success');
                } else {
                    const mailStatus = Number(data.status_send_mail);
                    const mailMessage = mailStatus === 200
                        ? 'Chứng chỉ đã được gửi đến email đăng ký. Bạn kiểm tra hộp thư đến và thư rác.'
                        : mailStatus === 500
                            ? 'Email chưa gửi thành công. Bạn vẫn có thể tải chứng chỉ PDF bên dưới; hãy liên hệ quản trị viên để kiểm tra email.'
                            : 'Chứng chỉ đang được gửi đến email đăng ký.';
                    this.alert(`Bạn đã hoàn thành bài thi với ${data.point}/${this.courseInfo.questions.length} điểm. ${mailMessage} Bấm Tải chứng chỉ PDF bên dưới để xem chứng chỉ.`, mailStatus === 500 ? 'warning' : 'success');
                }
            } else this.alert('Bạn chưa đạt điểm yêu cầu. Bạn có thể thử lại.', 'warning');
        }
    },
    methods: {
        alert(message,status) { this.$store.dispatch('showAlert',{message,status}); },
        submit() {
            if(this.isSubmit) return;
            if((this.courseInfo.questions || []).some(question => !this.selectedAnswers[question.id])) { this.alert('Vui lòng trả lời đầy đủ các câu hỏi.','warning'); return; }
            this.isSubmit = true; this.result = null;
            this.$store.dispatch(this.game ? 'sumbitCertificationGame' : 'sumbitCertification', {courseId:this.courseInfo.id,registration:this.registration,selectedAnswers:this.selectedAnswers});
        }
    }
};
</script>
<style scoped>
.registration-course { margin-bottom:24px; }
.registration-course label { display:block; margin-bottom:6px; }
.registration-course input { width:100%; padding:9px 12px; border:1px solid #aaa; border-radius:4px; background:#f5f5f5; }
.certificate-result { margin-top:20px; padding:16px; background:#eaf8ef; border:1px solid #9bceac; border-radius:6px; }
.certificate-result a { display:inline-block; margin-left:12px; font-weight:600; }
</style>
