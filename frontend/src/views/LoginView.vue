<template>
  <div class="login-container">
    <b-card class="login-card shadow-sm">
      <div class="text-center mb-2">
        <img src="@/assets/logo.png" height="80" />
      </div>
      <b-form @submit.prevent="handleLogin">
        <b-form-group label="Username" label-for="username" label-class="fw-bold">
          <b-input-group>
            <b-form-input
              id="username"
              v-model="username"
              type="text"
              placeholder="Enter your username"
              required
              autocomplete="username"
              trim
            ></b-form-input>
          </b-input-group>
        </b-form-group>

        <b-form-group label="Password" label-for="password" label-class="fw-bold" class="mt-3">
          <b-input-group>
            <b-form-input
              id="password"
              v-model="password"
              type="password"
              placeholder="Enter your password"
              required
              autocomplete="current-password"
            ></b-form-input>
          </b-input-group>
        </b-form-group>

        <b-button type="submit" class="w-100 mt-4 py-2 fw-bold login-btn" size="lg">
          Sign In
        </b-button>
      </b-form>
    </b-card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const username = ref('')
const password = ref('')
const authStore = useAuthStore()

const handleLogin = async () => {
  const success = await authStore.login(username.value, password.value)
  if (success) {
    await router.push('/notes')
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  padding: 20px;
}

.login-card {
  width: 500px;
  margin: 0 auto;
}

.login-btn {
  background-color: #ff971e;
  border-color: #ff971e;
}
</style>
