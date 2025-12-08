<template>
    <div class="container mt-4">
        <h3>Add Customer</h3>
        <form @submit.prevent="submitForm">
        <!-- Name -->
        <div class="form-group row align-items-center mb-3">
            <label class="col-sm-3 col-form-label">Name</label>
            <div class="col-sm-9 d-flex align-items-center">
                <i class="fas fa-user me-2"></i>
                <input type="text" v-model="customer.name" class="form-control" placeholder="Enter customer name"/>
            </div>
        </div>
        <!-- Email -->
        <div class="form-group row align-items-center mb-3">
            <label class="col-sm-3 col-form-label">Email</label>
            <div class="col-sm-9 d-flex align-items-center">
                <i class="fas fa-envelope me-2"></i>
                <input type="email" v-model="customer.email" class="form-control" placeholder="Enter email"/>
            </div>
        </div>

        <!-- Phone -->
        <div class="form-group row align-items-center mb-3">
            <label class="col-sm-3 col-form-label">Phone</label>
            <div class="col-sm-9 d-flex align-items-center">
                <i class="fas fa-phone me-2"></i>
                <input type="text" v-model="customer.phone" class="form-control" placeholder="Enter phone number" />
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-end">
            <button class="btn btn-primary" type="submit">Add Customer</button>
        </div>
    </form>
  </div>
</template>
  
<script>
    export default {
        data() {
            return {
                post: {}
            }
        },
        methods: {
            addPost() {
  
                this.axios
                    .post('http://127.0.0.1:8000/api/post/add', this.post)
                    .then(response => (
                        this.$router.push({name: 'home'})
                        // console.log(response.data)
                    ))
                    .catch(error => console.log(error))
                    .finally(() => this.loading = false)
            }
        }
    }
</script>