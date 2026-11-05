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
        $file = UploadedFile::fake()->create('reembolso.pdf', 20, 'application/pdf');

        $this->actingAs($firstUser)
            ->post(route('systems.reimbursements.documents.store', 1), ['document' => $file])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'El documento se guardó correctamente.');

        $document = SystemReimbursementDocument::query()->where('order_number', 1)->firstOrFail();
        $this->assertSame($file->getContent(), $document->content);
        $this->assertSame('reembolso.pdf', $document->original_name);
        Storage::disk('local')->assertEmpty();

        $this->actingAs($secondUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('reembolso.pdf')
            ->assertSee('Enviado a administradores')
            ->assertSee(route('systems.reimbursements.documents.download', 1), false)
            ->assertSee('Cancelar')
            ->assertDontSee('Cambiar documento')
            ->assertDontSee('Selecciona el nuevo documento');

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

    public function test_systems_users_can_replace_an_existing_reimbursement_document(): void
    {
        $this->seed(DepartmentSeeder::class);
        $systemsUser = $this->createUserWithDepartment('systems');
        $firstFile = UploadedFile::fake()->create('anterior.pdf', 20, 'application/pdf');
        $replacementFile = UploadedFile::fake()->create('actualizado.pdf', 20, 'application/pdf');

        $this->actingAs($systemsUser)
            ->post(route('systems.reimbursements.documents.store', 1), ['document' => $firstFile])
            ->assertRedirect(route('home'));

        $this->post(route('systems.reimbursements.documents.store', 1), ['document' => $replacementFile])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'El documento se guardó correctamente.');

        $this->assertDatabaseCount('system_reimbursement_documents', 1);
        $this->assertDatabaseHas('system_reimbursement_documents', [
            'order_number' => 1,
            'original_name' => 'actualizado.pdf',
            'content' => $replacementFile->getContent(),
        ]);
    }

    public function test_systems_users_can_cancel_a_saved_document_and_upload_another(): void
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
            ->delete(route('systems.reimbursements.documents.destroy', 1))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'El documento se eliminó correctamente.');

        $this->assertDatabaseMissing('system_reimbursement_documents', [
            'order_number' => 1,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Adjunta documento de reembolso')
            ->assertDontSee('reembolso.pdf');
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

        $this->get(route('admin.system-reimbursements.index'))
            ->assertForbidden();

        $this->get(route('systems.reimbursements.documents.download', 1))
            ->assertForbidden();

        $this->delete(route('systems.reimbursements.documents.destroy', 1))
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

        $this->delete(route('systems.reimbursements.documents.destroy', 1))
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
