<template>
  <div class="note-detail-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1>{{ isNewNote ? 'New Note' : 'Edit Note' }}</h1>
      <div>
        <b-button variant="outline-secondary" class="me-2" @click="goBack"> Cancel </b-button>
        <b-button class="save-btn" @click="saveNote" :disabled="isLoading || !note.title.trim()">
          <b-spinner v-if="isLoading" small></b-spinner>
          {{ isLoading ? 'Saving...' : 'Save' }}
        </b-button>
      </div>
    </div>

    <b-alert v-if="error" variant="danger" show dismissible @dismissed="error = null">
      {{ error }}
    </b-alert>

    <b-form @submit.prevent="saveNote">
      <b-form-group label="Title" :state="titleState">
        <b-form-input
          v-model="note.title"
          placeholder="Enter note title"
          required
          :disabled="isLoading"
          :state="titleState"
        ></b-form-input>
        <b-form-invalid-feedback v-if="validationErrors.title">
          {{ validationErrors.title }}
        </b-form-invalid-feedback>
        <b-form-text v-else>
          Title must be at least 5 characters long.
        </b-form-text>
      </b-form-group>

      <b-form-group label="Content" :state="contentState">
        <b-form-textarea
          v-model="note.content"
          placeholder="Enter note content"
          rows="10"
          :disabled="isLoading"
          :state="contentState"
        ></b-form-textarea>
        <b-form-invalid-feedback v-if="validationErrors.content">
          {{ validationErrors.content }}
        </b-form-invalid-feedback>
        <b-form-text v-else>
          Content must be at least 5 characters long.
        </b-form-text>
      </b-form-group>
    </b-form>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { type Note, useNotesStore } from '@/stores/notes'
import { ApiValidationError } from '@/utils/api'

interface ValidationErrors {
  title: string | null
  content: string | null
  [key: string]: string | null
}

const router = useRouter()
const route = useRoute()
const notesStore = useNotesStore()
const noteId = computed(() => route.params.id as string)
const isNewNote = computed(() => noteId.value === 'new')
const isLoading = ref(false)
const error = ref<string | null>(null)
const validationErrors = reactive<ValidationErrors>({
  title: null,
  content: null
})

const note = ref<Note>({
  id: 0,
  title: '',
  content: '',
  createdAt: '',
  updatedAt: ''
})

const titleState = computed(() => {
  if (!note.value.title) return null
  if (validationErrors.title) return false
  return note.value.title.length >= 5
})

const contentState = computed(() => {
  if (!note.value.content) return null
  if (validationErrors.content) return false
  return note.value.content.length >= 5
})

onMounted(async () => {
  if (!isNewNote.value) {
    isLoading.value = true
    error.value = null
    clearValidationErrors()

    try {
      const id = parseInt(noteId.value)
      const fetchedNote = await notesStore.getNote(id)

      if (fetchedNote) {
        note.value = {
          id: fetchedNote.id,
          title: fetchedNote.title,
          content: fetchedNote.content,
          updatedAt: fetchedNote.updatedAt,
          createdAt: fetchedNote.createdAt
        }
      } else {
        error.value = 'Note not found'
        setTimeout(() => {
          router.push('/notes')
        }, 3000)
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load note'
      console.error('Error loading note:', err)
    } finally {
      isLoading.value = false
    }
  }
})

/**
 * Resets the validation error messages for specific fields.
 */
const clearValidationErrors = () => {
  validationErrors.title = null
  validationErrors.content = null
}

/**
 * Validates the details of a note, ensuring the `title` and `content` fields have minimum required lengths.
 *
 * @returns {boolean} True if the note passes validation, otherwise false.
 */
const validateNote = (): boolean => {
  clearValidationErrors()

  let isValid = true

  if (!note.value.title || note.value.title.length < 5) {
    validationErrors.title = 'Title must be at least 5 characters long'
    isValid = false
  }

  if (!note.value.content || note.value.content.length < 5) {
    validationErrors.content = 'Content must be at least 5 characters long'
    isValid = false
  }

  return isValid
}

/**
 * Handles validation errors coming from API responses by mapping them to local validation errors.
 *
 * @param {any} err - The error object to be processed. Expected to be an instance of `ApiValidationError`.
 * @returns {boolean} - Returns `true` if the error was identified as an `ApiValidationError` and processed successfully, otherwise `false`.
 */
const handleApiValidationErrors = (err: any): boolean => {
  if (err instanceof ApiValidationError) {
    const fieldErrors = err.getFieldErrors()

    Object.entries(fieldErrors).forEach(([field, message]) => {
      if (field in validationErrors) {
        validationErrors[field] = message
      }
    })

    return true
  }

  return false
}

/**
 * Asynchronous function to save the current note, either by adding a new note or updating an existing one.
 */
const saveNote = async () => {
  error.value = null
  clearValidationErrors()

  if (!validateNote()) {
    return
  }

  isLoading.value = true

  try {
    if (isNewNote.value) {
      await notesStore.addNote(note.value.title, note.value.content)
    } else if (note.value.id) {
      await notesStore.updateNote(note.value.id, note.value.title, note.value.content)
    }

    await router.push('/notes')
  } catch (err: any) {
    console.error('Error saving note:', err)

    if (!handleApiValidationErrors(err)) {
      error.value = err instanceof Error ? err.message : 'Failed to save note'
    }
  } finally {
    isLoading.value = false
  }
}

/**
 * Navigates the user back to the "/notes" page.
 */
const goBack = () => {
  router.push('/notes')
}
</script>

<style scoped>
.note-detail-container {
  width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.save-btn {
  background-color: #ff971e;
  border-color: #ff971e;
}
</style>
