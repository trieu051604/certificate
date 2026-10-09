<template><loader-form-submit :provinces="provinces" :schools="schools" :course="course" game></loader-form-submit></template>
<script>
import LoaderFormSubmit from './LoaderFormSubmit.vue';
export default {components:{LoaderFormSubmit},props:{provinces:String,schools:String,course:String}};
</script>
