import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/utils/api'

interface Note {
  id: number
  title: string
  content: string
  createdAt: string
  updatedAt: string
}

export const useNotesStore = defineStore('notes', () => {
  const notes = ref<Note[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  async function fetchNotes() {
    isLoading.value = true
    error.value = null

    try {
      notes.value = await api.get<Note[]>('api/notes')
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
    } finally {
      isLoading.value = false
    }
  }

  async function addNote(title: string, content: string) {
    isLoading.value = true
    error.value = null

    try {
      const newNote = await api.post<Note>('api/note', { title, content })
      notes.value.push(newNote)
      return newNote
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function updateNote(id: number, title: string, content: string) {
    isLoading.value = true
    error.value = null

    try {
      const updatedNote = await api.put<Note>(`api/note/${id}`, { title, content })

      const index = notes.value.findIndex((n) => n.id === id)
      if (index !== -1) {
        notes.value[index] = updatedNote
      }

      return updatedNote
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function deleteNote(id: number) {
    isLoading.value = true
    error.value = null

    try {
      await api.delete(`api/note/${id}`)

      notes.value = notes.value.filter((note) => note.id !== id)
      return true
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function getNote(id: number) {
    const existingNote = notes.value.find((note) => note.id === id)
    if (existingNote) {
      return existingNote
    }

    isLoading.value = true
    error.value = null

    try {
      return await api.get<Note>(`api/note/${id}`)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Unknown error occurred'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  function clearNotes() {
    notes.value = []
  }

  return {
    notes,
    isLoading,
    error,
    fetchNotes,
    addNote,
    updateNote,
    deleteNote,
    getNote,
    clearNotes,
  }
})
