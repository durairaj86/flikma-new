<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * "Approve selected" on a list's Draft tab: every ticked document is approved through the controller's own updateStatus(),
 * one at a time, so each one gets exactly the checks and posting a single Approve gives it (balance, closed period, stock of
 * credit, links ...). A refusal only skips that document; the reply lists what was approved and why the others were not.
 *
 * The controller says which documents qualify with bulkApproveTarget():
 *   ['model' => Model::class, 'draft' => [1], 'approved' => 2, 'number' => 'row_no']   (+ 'query' when the screen sees only part of the table)
 * Only documents still in one of the draft statuses are touched, so a page that is out of date cannot approve twice.
 * The route path ends in /bulk/approve, which the rights middleware maps to the Approve right of the section.
 */
trait BulkApproves
{
    abstract protected function bulkApproveTarget(): array;

    public function bulkApprove(Request $request): JsonResponse
    {
        $ids = $request->validate([
            'ids' => 'required|array|min:1|max:200',
            'ids.*' => 'integer|min:1',
        ])['ids'];

        $target = $this->bulkApproveTarget();
        $documents = ($target['query'] ?? $target['model']::query())->whereIn('id', array_unique($ids))->get();
        $approved = [];
        $failed = [];

        foreach ($documents as $document) {
            $number = $document->{$target['number']} ?: '#' . $document->id;
            $status = $document->status instanceof \BackedEnum ? $document->status->value : $document->status;
            if (!in_array((int) $status, $target['draft'], true)) {
                $failed[] = ['id' => $document->id, 'number' => $number, 'message' => __('It is no longer a draft.')];
                continue;
            }
            [$ok, $message] = $this->approveOne($document->id, $target['approved']);
            if ($ok) {
                $approved[] = ['id' => $document->id, 'number' => $number];
            } else {
                $failed[] = ['id' => $document->id, 'number' => $number, 'message' => $message];
            }
        }
        // ids of another company (or deleted) are not found by the scoped query above
        foreach (array_diff(array_unique($ids), $documents->pluck('id')->all()) as $missing) {
            $failed[] = ['id' => (int) $missing, 'number' => '#' . $missing, 'message' => __('Not found.')];
        }

        return response()->json([
            'status' => $approved ? 'success' : 'error',
            'message' => __(':ok approved, :failed not approved', ['ok' => count($approved), 'failed' => count($failed)]),
            'approved' => $approved,
            'failed' => $failed,
        ], $approved || !$failed ? 200 : 422);
    }

    /** Runs the single Approve and reads its answer: [approved?, message]. */
    private function approveOne(int $id, int $approvedStatus): array
    {
        $level = DB::transactionLevel();
        try {
            $result = $this->readAnswer($this->callUpdateStatus($id, $approvedStatus));
        } catch (ValidationException $e) {
            $result = [false, collect($e->errors())->flatten()->first() ?: $e->getMessage()];
        } catch (HttpExceptionInterface $e) {
            $result = [false, $e->getMessage() ?: __('Not allowed.')];
        } catch (\Throwable $e) {
            report($e);
            $result = [false, __('Could not be approved: :error', ['error' => $e->getMessage()])];
        }

        // some refusals return from inside the action's transaction without closing it (a single request ends there, so it rolls
        // back on its own); here the next documents would run inside it and be undone with it. Close it, and since whatever it
        // held is undone, the document is not approved.
        if (DB::transactionLevel() > $level) {
            while (DB::transactionLevel() > $level) {
                DB::rollBack();
            }
            if ($result[0]) {
                $result = [false, __('Not approved.')];
            }
        }

        return $result;
    }

    private function callUpdateStatus(int $id, int $approvedStatus)
    {
        $method = new \ReflectionMethod($this, 'updateStatus');
        $first = $method->getParameters()[0] ?? null;
        $takesRequest = $first && $first->getType() && !$first->getType()->isBuiltin()
            && is_a($first->getType()->getName(), Request::class, true);

        return $takesRequest ? $this->updateStatus(request(), $id, $approvedStatus) : $this->updateStatus($id, $approvedStatus);
    }

    private function readAnswer($response): array
    {
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            $message = $data['message'] ?? null;
            $message = is_array($message) ? implode(' ', array_filter($message, 'is_string')) : $message;
            if ($response->getStatusCode() >= 400 || in_array($data['status'] ?? null, ['error', false], true) || ($data['success'] ?? true) === false) {
                return [false, $message ?: __('Not approved.')];
            }
        }

        return [true, null];
    }
}
