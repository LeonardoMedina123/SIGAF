<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SystemReimbursementDocument;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemReimbursementDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_systems_users_can_access_a_document_uploaded_by_another_user(): void
    {
        Storage::fake('local');
        $this->seed(DepartmentSeeder::class);
        $firstUser = $this->createUserWithDepartment('systems');
        $secondUser = $this->createUserWithDepartment('systems');
        $administrator = $this->createUserWithDepartment('administrator');
        $file = UploadedFile::fake()->create('reembolso.pdf', 20, 'application/pdf');

        $this->actingAs($firstUser)
            ->post(route('systems.reimbursements.documents.store', 1), ['document' => $file])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'El documento se guardó correctamente.');

        $document = SystemReimbursementDocument::query()->where('order_number', 1)->firstOrFail();
        $this->assertSame($file->getContent(), $document->content);
        $this->assertSame('reembolso.pdf', $document->original_name);
        $this->assertSame(1, $administrator->unreadNotifications()->count());
        $this->assertSame(
            'system_reimbursement_uploaded',
            $administrator->unreadNotifications()->firstOrFail()->data['event'],
        );
        Storage::disk('local')->assertEmpty();

        $this->actingAs($secondUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('reembolso.pdf')
            ->assertSee('Enviado a administradores')
            ->assertSee(route('systems.reimbursements.documents.download', 1), false)
            ->assertSee('Edición desactivada por administración');

        $this->get(route('systems.reimbursements.documents.download', 1))
            ->assertDownload('reembolso-1.pdf')
            ->assertStreamedContent($file->getContent());
    }

    public function test_administrators_can_view_and_download_system_reimbursement_documents(): void
    {
        $this->seed(DepartmentSeeder::class);
        $administrator = $this->createUserWithDepartment('administrator');
        SystemReimbursementDocument::query()->create([
            'order_number' => 1,
            'original_name' => 'reembolso-sistemas.pdf',
            'mime_type' => 'application/pdf',
            'content' => 'PDF content',
        ]);

        $this->actingAs($administrator)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Reembolsos de Sistemas')
            ->assertDontSee('REEMBOLSOS DEL DEPARTAMENTO DE SISTEMAS');

        $this->get(route('admin.system-reimbursements.index'))
            ->assertOk()
            ->assertSee('Reembolsos del Departamento de Sistemas')
            ->assertSee('REEMBOLSO')
            ->assertSee('reembolso-sistemas.pdf')
            ->assertSee(route('systems.reimbursements.documents.download', 1), false)
            ->assertSee('Recibido');

        $this->get(route('systems.reimbursements.documents.download', 1))
            ->assertDownload('reembolso-1.pdf')
            ->assertStreamedContent('PDF content');
    }

    public function test_systems_users_can_replace_a_document_only_when_an_administrator_enables_editing(): void
    {
        $this->seed(DepartmentSeeder::class);
        $systemsUser = $this->createUserWithDepartment('systems');
        $administrator = $this->createUserWithDepartment('administrator');
        $firstFile = UploadedFile::fake()->create('anterior.pdf', 20, 'application/pdf');
        $replacementFile = UploadedFile::fake()->create('actualizado.pdf', 20, 'application/pdf');

        $this->actingAs($systemsUser)
            ->post(route('systems.reimbursements.documents.store', 1), ['document' => $firstFile])
            ->assertRedirect(route('home'));

        $this->actingAs($systemsUser)
            ->post(route('systems.reimbursements.documents.store', 1), ['document' => $replacementFile])
            ->assertForbidden();

        $this->assertDatabaseHas('system_reimbursement_documents', [
            'order_number' => 1,
            'original_name' => 'anterior.pdf',
            'editing_enabled' => false,
        ]);

        $this->actingAs($administrator)
            ->post(route('admin.systems.reimbursements.documents.toggle-editing', 1))
            ->assertRedirect(route('admin.system-reimbursements.index'))
            ->assertSessionHas('success', 'Se habilitó la edición del documento de reembolso.');

        $this->actingAs($systemsUser)
            ->post(route('systems.reimbursements.documents.store', 1), ['document' => $replacementFile])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'El documento se guardó correctamente.');

        $this->assertDatabaseHas('system_reimbursement_documents', [
            'order_number' => 1,
            'original_name' => 'actualizado.pdf',
            'editing_enabled' => true,
        ]);
    }

    public function test_administrators_can_toggle_document_editing_per_reimbursement(): void
    {
        $this->seed(DepartmentSeeder::class);
        $administrator = $this->createUserWithDepartment('administrator');
        SystemReimbursementDocument::query()->create([
            'order_number' => 1,
            'original_name' => 'reembolso.pdf',
            'mime_type' => 'application/pdf',
            'content' => 'PDF content',
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.system-reimbursements.index'))
            ->assertOk()
            ->assertSee('Activar edición');

        $this->post(route('admin.systems.reimbursements.documents.toggle-editing', 1))
            ->assertRedirect(route('admin.system-reimbursements.index'));

        $this->assertDatabaseHas('system_reimbursement_documents', [
            'order_number' => 1,
            'editing_enabled' => true,
        ]);

        $this->post(route('admin.systems.reimbursements.documents.toggle-editing', 1))
            ->assertRedirect(route('admin.system-reimbursements.index'));

        $this->assertDatabaseHas('system_reimbursement_documents', [
            'order_number' => 1,
            'editing_enabled' => false,
        ]);
    }

    public function test_systems_users_cannot_replace_a_document_after_editing_is_disabled(): void
    {
        $this->seed(DepartmentSeeder::class);
        $systemsUser = $this->createUserWithDepartment('systems');
        $administrator = $this->createUserWithDepartment('administrator');
        SystemReimbursementDocument::query()->create([
            'order_number' => 1,
            'original_name' => 'anterior.pdf',
            'mime_type' => 'application/pdf',
            'content' => 'Original PDF',
            'editing_enabled' => true,
        ]);
        $replacementFile = UploadedFile::fake()->create('actualizado.pdf', 20, 'application/pdf');

        $this->actingAs($administrator)
            ->post(route('admin.systems.reimbursements.documents.toggle-editing', 1))
            ->assertRedirect(route('admin.system-reimbursements.index'));

        $this->post(route('systems.reimbursements.documents.store', 1), ['document' => $replacementFile])
            ->assertForbidden();

        $this->assertDatabaseHas('system_reimbursement_documents', [
            'order_number' => 1,
            'original_name' => 'anterior.pdf',
            'content' => 'Original PDF',
            'editing_enabled' => false,
        ]);
    }

    public function test_systems_users_cannot_delete_a_document_to_bypass_the_editing_toggle(): void
    {
        $this->seed(DepartmentSeeder::class);
        $systemsUser = $this->createUserWithDepartment('systems');
        SystemReimbursementDocument::query()->create([
            'order_number' => 1,
            'original_name' => 'reembolso.pdf',
            'mime_type' => 'application/pdf',
            'content' => 'PDF content',
        ]);

        $this->actingAs($systemsUser)
            ->delete('/systems/reimbursements/1/document')
            ->assertMethodNotAllowed();

        $this->assertDatabaseHas('system_reimbursement_documents', [
            'order_number' => 1,
            'original_name' => 'reembolso.pdf',
        ]);
    }

    public function test_users_outside_systems_cannot_upload_or_download_documents(): void
    {
        $this->seed(DepartmentSeeder::class);
        $user = $this->createUserWithDepartment('suppliers');
        SystemReimbursementDocument::query()->create([
            'order_number' => 1,
            'original_name' => 'reembolso.pdf',
            'mime_type' => 'application/pdf',
            'content' => 'PDF content',
        ]);

        $this->actingAs($user)
            ->post(route('systems.reimbursements.documents.store', 1), [
                'document' => UploadedFile::fake()->create('nuevo.pdf', 1, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->post(route('admin.systems.reimbursements.documents.toggle-editing', 1))
            ->assertForbidden();

        $this->get(route('admin.system-reimbursements.index'))
            ->assertForbidden();

        $this->get(route('systems.reimbursements.documents.download', 1))
            ->assertForbidden();

        $this->assertDatabaseCount('system_reimbursement_documents', 1);
    }

    public function test_upload_rejects_non_pdf_documents(): void
    {
        $this->seed(DepartmentSeeder::class);
        $user = $this->createUserWithDepartment('systems');

        $this->actingAs($user)
            ->post(route('systems.reimbursements.documents.store', 1), [
                'document' => UploadedFile::fake()->create('reembolso.txt', 1, 'text/plain'),
            ])
            ->assertInvalid(['document' => 'El documento debe ser un archivo PDF.']);

        $this->assertDatabaseCount('system_reimbursement_documents', 0);
    }

    public function test_upload_requires_a_document(): void
    {
        $this->seed(DepartmentSeeder::class);
        $user = $this->createUserWithDepartment('systems');

        $this->actingAs($user)
            ->post(route('systems.reimbursements.documents.store', 1))
            ->assertInvalid(['document' => 'Selecciona un documento PDF.']);

        $this->assertDatabaseCount('system_reimbursement_documents', 0);
    }

    public function test_upload_rejects_documents_larger_than_10_mb(): void
    {
        $this->seed(DepartmentSeeder::class);
        $user = $this->createUserWithDepartment('systems');

        $this->actingAs($user)
            ->post(route('systems.reimbursements.documents.store', 1), [
                'document' => UploadedFile::fake()->create('reembolso.pdf', 10241, 'application/pdf'),
            ])
            ->assertInvalid(['document' => 'El documento no puede superar 10 MB.']);

        $this->assertDatabaseCount('system_reimbursement_documents', 0);
    }

    public function test_document_routes_require_authentication(): void
    {
        $this->post(route('systems.reimbursements.documents.store', 1))
            ->assertRedirect(route('login'));

        $this->get(route('systems.reimbursements.documents.download', 1))
            ->assertRedirect(route('login'));

        $this->post(route('admin.systems.reimbursements.documents.toggle-editing', 1))
            ->assertRedirect(route('login'));

        $this->get(route('admin.system-reimbursements.index'))
            ->assertRedirect(route('login'));
    }

    private function createUserWithDepartment(string $slug): User
    {
        return User::factory()->create([
            'department_id' => Department::query()->where('slug', $slug)->value('id'),
        ]);
    }
}
