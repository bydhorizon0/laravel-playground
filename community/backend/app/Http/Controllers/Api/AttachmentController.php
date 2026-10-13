<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadAttachmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

use function now;

#[Middleware('auth:sanctum', only: ['upload'])]
class AttachmentController extends Controller
{
    public function upload(UploadAttachmentRequest $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');

        $filePath = $file->store('uploads/'.now()->format('Y-m').'/'.now()->format('d'), 'public');

        // uploads/YYYY-MM 디렉터리에 분할 저장
        // store()는 충돌하지 않는 랜덤 파일명을 만들고, 확장자는 클라이언트가 보낸 이름이 아니라
        // 파일 내용(MIME)으로 추정한 값을 사용한다. (getClientOriginalExtension()은 조작 가능하므로 쓰지 않는다.)
        if ($filePath === null) {
            return $this->errorResponse('파일 저장에 실패했습니다.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 디스크 설정(config/filesystems.php)의 url 기준으로 공개 URL을 생성한다.
        // asset('storage/...') 대신 Storage::url()을 쓰면 S3 등 다른 디스크로 바꿔도 코드를 수정하지 않아도 된다.
        $fileUrl = Storage::disk('public')->url($filePath);

        return $this->createdResponse([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ], '파일이 성공적으로 업로드되었습니다.');
    }
}
