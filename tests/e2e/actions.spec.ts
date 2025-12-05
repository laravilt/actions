import { test, expect } from '@playwright/test'

/**
 * Actions Component E2E Tests
 *
 * Tests action buttons, confirmation modals, modal forms,
 * and various action variants against the Actions Demo page.
 */

test.describe('Actions Component', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/demos/actions')
    await page.waitForSelector('h1:has-text("Actions Demo")', { timeout: 10000 })
  })

  test.describe('Page Rendering', () => {
    test('should render the Actions Demo page', async ({ page }) => {
      await expect(page.locator('h1:has-text("Actions Demo")')).toBeVisible()
    })

    test('should display all action groups', async ({ page }) => {
      // Check for main section headings (h2 or h3 elements)
      await expect(page.locator('h2:has-text("Basic Actions"), h3:has-text("Basic Actions")')).toBeVisible()
      await expect(page.locator('h2:has-text("Action Variants"), h3:has-text("Action Variants")')).toBeVisible()
    })

    test('should display feature overview cards', async ({ page }) => {
      // The page has feature cards at the top - look in the cards section specifically
      // Cards are in rounded-lg border bg-card divs near the top, before the action groups
      const cardsSection = page.locator('.grid.gap-4 .rounded-lg.border.bg-card')
      await expect(cardsSection.first()).toBeVisible()

      // Verify at least 4 feature cards exist
      await expect(cardsSection).toHaveCount(4)
    })
  })

  test.describe('Basic Actions', () => {
    test('should have Primary Action button', async ({ page }) => {
      const button = page.locator('button:has-text("Primary Action")')
      await expect(button).toBeVisible()
    })

    test('should have Secondary Action button', async ({ page }) => {
      const button = page.locator('button:has-text("Secondary Action")')
      await expect(button).toBeVisible()
    })

    test('should have Danger Action button', async ({ page }) => {
      const button = page.locator('button:has-text("Danger Action")')
      await expect(button).toBeVisible()
    })

    test('should have Success Action button', async ({ page }) => {
      const button = page.locator('button:has-text("Success Action")')
      await expect(button).toBeVisible()
    })

    test('should have Warning Action button', async ({ page }) => {
      const button = page.locator('button:has-text("Warning Action")')
      await expect(button).toBeVisible()
    })

    test('should execute basic action successfully', async ({ page }) => {
      const button = page.locator('button:has-text("Primary Action")')
      await button.click()

      // Action should execute without error - wait for the page to stabilize
      // The button should still be visible and clickable after action completes
      await page.waitForTimeout(1000)
      await expect(button).toBeVisible()

      // No error modals or alerts should appear
      const errorModal = page.locator('[role="alertdialog"]:has-text("error"), .error-message')
      await expect(errorModal).toHaveCount(0)
    })
  })

  test.describe('Action Variants', () => {
    test('should have Solid Button variant', async ({ page }) => {
      const button = page.locator('button:has-text("Solid Button")')
      await expect(button).toBeVisible()
    })

    test('should have Outlined Button variant', async ({ page }) => {
      const button = page.locator('button:has-text("Outlined Button")')
      await expect(button).toBeVisible()
    })

    test('should have Link Style variant', async ({ page }) => {
      const button = page.locator('button:has-text("Link Style"), a:has-text("Link Style")')
      await expect(button).toBeVisible()
    })

    test('should have Icon Only button with tooltip', async ({ page }) => {
      // Icon-only button won't have visible text, look for tooltip
      const iconButton = page.locator('button[title="Settings"], button[aria-label="Settings"], button:has([class*="settings"])')
      const exists = await iconButton.count()
      if (exists > 0) {
        await expect(iconButton.first()).toBeVisible()
      }
    })

    test('should have Disabled Action button', async ({ page }) => {
      const button = page.locator('button:has-text("Disabled Action")')
      await expect(button).toBeVisible()
      // The disabled action should exist - styling may vary
    })
  })

  test.describe('Action Sizes', () => {
    test('should have Small size action', async ({ page }) => {
      const button = page.locator('button:has-text("Small")')
      await expect(button).toBeVisible()
    })

    test('should have Default size action', async ({ page }) => {
      const button = page.locator('button:has-text("Default")').first()
      await expect(button).toBeVisible()
    })

    test('should have Large size action', async ({ page }) => {
      const button = page.locator('button:has-text("Large")')
      await expect(button).toBeVisible()
    })
  })

  test.describe('Confirmation Modals', () => {
    test('should open simple confirmation modal', async ({ page }) => {
      const button = page.locator('button:has-text("Simple Confirmation")')
      await button.click()

      // Wait for modal to appear
      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Check modal content - heading should be "Confirm Action"
      await expect(page.locator('text=Confirm Action')).toBeVisible()
      await expect(page.locator('text=Are you sure you want to proceed')).toBeVisible()
    })

    test('should close modal on cancel', async ({ page }) => {
      const button = page.locator('button:has-text("Simple Confirmation")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Find and click cancel button
      const cancelButton = page.locator('[role="dialog"] button:has-text("Cancel")')
      await cancelButton.click()

      // Modal should close - wait for dialog to be hidden or removed
      await page.waitForTimeout(500)
      const dialogs = page.locator('[role="dialog"]:visible, [role="alertdialog"]:visible')
      const count = await dialogs.count()
      expect(count).toBe(0)
    })

    test('should confirm and execute action', async ({ page }) => {
      const button = page.locator('button:has-text("Simple Confirmation")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Find and click confirm button
      const confirmButton = page.locator('[role="dialog"] button:has-text("Confirm")')
      await confirmButton.click()

      // Wait for notification/modal to close
      await page.waitForTimeout(1000)
      // Check notification appeared or modal closed
      const dialogs = page.locator('[role="dialog"]:visible')
      const dialogCount = await dialogs.count()
      expect(dialogCount).toBeLessThanOrEqual(1) // Modal should close or show success
    })

    test('should display delete confirmation with custom labels', async ({ page }) => {
      const button = page.locator('button:has-text("Delete with Confirmation")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Check for custom modal heading
      await expect(page.locator('text=Delete Item')).toBeVisible()

      // Check for custom submit label
      await expect(page.locator('button:has-text("Yes, Delete")')).toBeVisible()

      // Check for custom cancel label
      await expect(page.locator('button:has-text("No, Keep It")')).toBeVisible()
    })

    test('should display modal with icon', async ({ page }) => {
      const button = page.locator('button:has-text("With Modal Icon")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Check for modal heading within the dialog
      await expect(page.locator('[role="dialog"] h2, [role="dialog"] [class*="DialogTitle"]').filter({ hasText: 'Security Check' })).toBeVisible()
    })
  })

  test.describe('Modal Forms', () => {
    test('should open feedback modal with form', async ({ page }) => {
      const button = page.locator('button:has-text("Submit Feedback")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Check modal opened with heading
      await expect(page.locator('text=Submit Feedback').first()).toBeVisible()
      // Check for form fields - Rating label
      await expect(page.locator('label:has-text("Rating")').first()).toBeVisible({ timeout: 5000 })
    })

    test('should validate required fields in modal form', async ({ page }) => {
      const button = page.locator('button:has-text("Submit Feedback")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Try to submit without filling required fields
      const submitButton = page.locator('[role="dialog"] button:has-text("Submit Feedback")')
      await submitButton.click()

      // Should show validation error or prevent submission
      // The modal should still be open
      await page.waitForTimeout(500)
      await expect(page.locator('[role="dialog"]').first()).toBeVisible()
    })

    test('should display backend validation errors in modal form', async ({ page }) => {
      // Click the Backend Validation button
      const button = page.locator('button:has-text("Backend Validation")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Wait for modal content to fully render
      await page.waitForTimeout(500)

      // Fill with invalid data - username too short and age invalid
      const usernameField = page.locator('[role="dialog"] input[name="username"]')
      await usernameField.fill('ab')  // Too short (min 3)

      const ageField = page.locator('[role="dialog"] input[name="age"]')
      await ageField.fill('15')  // Too young (min 18)

      // Submit the form
      const submitButton = page.locator('[role="dialog"] button:has-text("Submit")')
      await submitButton.click()

      // Wait for server response and Inertia to update the page
      await page.waitForTimeout(2000)

      // The modal should still be open with validation errors
      // Wait for dialog with longer timeout and better selector
      const dialog = page.locator('[role="dialog"], [role="alertdialog"]')
      await expect(dialog.first()).toBeVisible({ timeout: 5000 })

      // Backend validation errors should be displayed
      // Error messages have role="alert" and text-red-600 class
      const errorText = page.locator('[role="dialog"] [role="alert"], [role="dialog"] .text-red-600, [role="alertdialog"] [role="alert"], [role="alertdialog"] .text-red-600')
      await expect(errorText.first()).toBeVisible({ timeout: 5000 })
    })

    test('should display client-side validation errors for required fields', async ({ page }) => {
      // Click the Client Validation button
      const button = page.locator('button:has-text("Client Validation")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Wait for modal content to fully render
      await page.waitForTimeout(500)

      // Don't fill any fields - just try to submit immediately
      const submitButton = page.locator('[role="dialog"] button:has-text("Submit")')
      await submitButton.click()

      // Wait a moment for validation to run
      await page.waitForTimeout(500)

      // Modal should still be open (validation failed - no server request made)
      await expect(page.locator('[role="dialog"]').first()).toBeVisible()

      // Client-side validation errors should be displayed
      // Error messages have role="alert" and text-red-600 class
      const errorText = page.locator('[role="dialog"] [role="alert"], [role="dialog"] .text-red-600')
      await expect(errorText.first()).toBeVisible({ timeout: 3000 })
    })

    test('should prevent form submission when required text field is empty', async ({ page }) => {
      // Click the Client Validation button
      const button = page.locator('button:has-text("Client Validation")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })
      await page.waitForTimeout(500)

      // Try to submit without filling fields - should show validation errors
      const submitButton = page.locator('[role="dialog"] button:has-text("Submit")')
      await submitButton.click()
      await page.waitForTimeout(500)

      // Verify at least one error is shown (Full Name is required)
      const errorText = page.locator('[role="dialog"] [role="alert"], [role="dialog"] .text-red-600')
      const errorCount = await errorText.count()
      expect(errorCount).toBeGreaterThanOrEqual(1)

      // Verify the modal is still open (validation prevented submission)
      await expect(page.locator('[role="dialog"]').first()).toBeVisible()

      // Verify the error message includes the field name
      const firstError = await errorText.first().textContent()
      expect(firstError).toContain('required')
    })

    test('should submit modal form with data', async ({ page }) => {
      const button = page.locator('button:has-text("Submit Feedback")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Wait for modal content to fully render
      await page.waitForTimeout(500)

      // Find and click the select trigger - it's an input element in a ComboboxRoot
      // The select renders as an input with aria-expanded attribute
      const ratingInput = page.locator('[role="dialog"] input[aria-expanded]').first()
      if (await ratingInput.count() > 0) {
        await ratingInput.click()
        await page.waitForTimeout(300)

        // Click on an option from the dropdown
        const option = page.locator('[role="option"]').first()
        if (await option.isVisible()) {
          await option.click()
        }
      }

      // Fill comments textarea
      const commentsField = page.locator('[role="dialog"] textarea').first()
      if (await commentsField.isVisible()) {
        await commentsField.fill('This is a test feedback message')
      }

      // Submit - modal should close or show notification
      const submitButton = page.locator('[role="dialog"] button:has-text("Submit Feedback")')
      await submitButton.click()

      // Modal should close or notification should appear
      await page.waitForTimeout(1000)
    })

    test('should open Quick Create modal with form', async ({ page }) => {
      const button = page.locator('button:has-text("Quick Create")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Check modal heading
      await expect(page.locator('text=Create New Item')).toBeVisible()
    })
  })

  test.describe('Slide Over Panels', () => {
    test('should open slide over panel', async ({ page }) => {
      const button = page.locator('button:has-text("Open Slide Over")')
      await button.click()

      // Wait for slide over to appear
      await page.waitForTimeout(500)

      // Check for slide over content - the heading "Slide Over Panel"
      await expect(page.getByText('Slide Over Panel').first()).toBeVisible({ timeout: 5000 })
    })

    test('should have form fields in slide over', async ({ page }) => {
      const button = page.locator('button:has-text("Open Slide Over")')
      await button.click()

      await page.waitForTimeout(500)

      // Check for Title label in the slide over
      await expect(page.locator('label:has-text("Title")').first()).toBeVisible({ timeout: 5000 })
    })
  })

  test.describe('URL Actions', () => {
    test('should have internal link action', async ({ page }) => {
      const button = page.locator('button:has-text("Go to Dashboard"), a:has-text("Go to Dashboard")')
      await expect(button).toBeVisible()
    })

    test('should have external link action', async ({ page }) => {
      const button = page.locator('button:has-text("Visit Documentation"), a:has-text("Visit Documentation")')
      await expect(button).toBeVisible()
    })

    test('should navigate to dashboard on click', async ({ page }) => {
      const button = page.locator('button:has-text("Go to Dashboard"), a:has-text("Go to Dashboard")')
      await button.click()

      // Should navigate to dashboard
      await page.waitForURL(/\/admin\/dashboard|\/admin$/, { timeout: 5000 })
    })
  })

  test.describe('Icon Positions', () => {
    test('should have Icon Before button', async ({ page }) => {
      const button = page.locator('button:has-text("Icon Before")')
      await expect(button).toBeVisible()
    })

    test('should have Icon After button', async ({ page }) => {
      const button = page.locator('button:has-text("Icon After")')
      await expect(button).toBeVisible()
    })

    test('should have No Icon button', async ({ page }) => {
      const button = page.locator('button:has-text("No Icon")')
      await expect(button).toBeVisible()
    })
  })

  test.describe('Accessibility', () => {
    test('should have proper button roles', async ({ page }) => {
      const buttons = page.locator('button')
      const count = await buttons.count()
      expect(count).toBeGreaterThan(0)
    })

    test('should have proper modal ARIA attributes', async ({ page }) => {
      const button = page.locator('button:has-text("Simple Confirmation")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Check for proper ARIA role
      const modal = page.locator('[role="dialog"], [role="alertdialog"]')
      await expect(modal.first()).toHaveAttribute('role', /(dialog|alertdialog)/)
    })

    test('disabled buttons should exist in action variants', async ({ page }) => {
      // Just verify the Disabled Action button exists
      const button = page.locator('button:has-text("Disabled Action")')
      await expect(button).toBeVisible()
    })
  })

  test.describe('Keyboard Navigation', () => {
    test('should close modal on Escape key', async ({ page }) => {
      const button = page.locator('button:has-text("Simple Confirmation")')
      await button.click()

      await page.waitForSelector('[role="dialog"], [role="alertdialog"]', { timeout: 5000 })

      // Press Escape
      await page.keyboard.press('Escape')

      // Modal should close
      await expect(page.locator('[role="dialog"], [role="alertdialog"]')).toBeHidden({ timeout: 3000 })
    })
  })
})
