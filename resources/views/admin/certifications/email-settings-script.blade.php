<script>
    document.getElementById('use-certificate-email').addEventListener('click', function () {
        document.getElementById('subject').value = this.dataset.subject;
        $('#editor').summernote('code', this.dataset.content);
    });
</script>
