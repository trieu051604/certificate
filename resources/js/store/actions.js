import Vue from 'vue'
import Swal from 'sweetalert2';

let loader = null;
const rootUrl = window.location.origin;

function addLoader(){
    loader = Vue.prototype.$loading({
        lock: true,
        text: 'Loading',
        spinner: 'el-icon-loading',
        background: 'rgba(0, 0, 0, 0.7)'
    })
    // console.log(loader)
}

function removeLoader(){
    loader.close();
}

// function notify(title,content){
//     Vue.prototype.$notify({
//         title: title == 200 ? 'Thành công' : 'Thất bại',
//         dangerouslyUseHTMLString: true,
//         message: content
//     });
// }

export const sumbitCertification = ({commit}, payload) => {
    addLoader()

    axios.post(rootUrl + '/api/certification/submit-form', payload)
        .then(res => {

            if(res.status == 200 && res.data.status == 201){
                return commit("setCertification", res.data.data);
            } else {
                return commit("setCertification", {
                    error: res.data.msg
                });
            }
    
        })
        .catch(error => {
            return commit("setCertification", {
                error: "Có lỗi xảy ra, vui lòng thử lại"
            });
        })
        .finally(() => {
            removeLoader(); // Tắt loader khi yêu cầu hoàn tất
        });
 
}

export const sumbitCertificationGame = ({commit}, payload) => {
    addLoader()

    axios.post(rootUrl + '/api/certification/submit-form-game', payload)
        .then(res => {

            if(res.status == 200 && res.data.status == 201){
                return commit("setCertification", res.data.data);
            } else {
                console.log(res)
                return commit("setCertification", {
                    error: res.data.msg || "Có lỗi xảy ra, vui lòng thử lại"
                });
            }
    
        })
        .catch(error => {
            return commit("setCertification", {
                error: "Có lỗi xảy ra, vui lòng thử lại"
            });
        })
        .finally(() => {
            removeLoader(); // Tắt loader khi yêu cầu hoàn tất
        });
 
}

export const showAlert = ({ commit }, payload) => {
    const { message, status } = payload
    commit('setMessage', payload);
    Swal.fire({
        title: 'Thông báo',
        text: message,
        icon: status,
        confirmButtonText: 'OK'
    });
}

export const showAlertRedirect = ({ commit }, payload) => {
    const { message, status, url } = payload
    commit('setMessage', payload);
    Swal.fire({
        title: 'Thông báo',
        text: message,
        icon: status,
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isDismissed || result.isConfirmed) {
            window.location.href = url;
        }
    });
}
