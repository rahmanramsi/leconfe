<script>
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({fail}) => {
            fail(({status,preventDefault}) => {
                if(status == 500){
                    alert('There something error happened, if this still occured please report to administrator. Error: ' + status);
                    preventDefault();
                }
            })
        })
    })
</script>
