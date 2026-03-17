import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Button from '../Button.vue'

describe('Button Component', () => {
  it('renders properly with text prop', () => {
    const wrapper = mount(Button, {
      props: { text: 'Kirim Pengajuan' }
    })
    expect(wrapper.text()).toContain('Kirim Pengajuan')
  })

  it('is disabled when isLoading prop is true', () => {
    const wrapper = mount(Button, {
      props: { isLoading: true }
    })
    expect(wrapper.attributes()).toHaveProperty('disabled')
  })
})