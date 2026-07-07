<?php
class LegalController extends BaseController
{
    public function show(): void
    {
        $section = $_GET['section'] ?? 'cgu';
        $validSections = ['cgu', 'privacy', 'cookies', 'legal'];

        if (!in_array($section, $validSections)) {
            $section = 'cgu';
        }

        $this->render('admin/legals', [
            'pageTitle' => $this->getTitle($section) . ' — MenuCraft',
            'section' => $section,
        ]);
    }

    private function getTitle(string $section): string
    {
        return match ($section) {
            'cgu' => 'Conditions Générales d\'Utilisation',
            'privacy' => 'Politique de Confidentialité',
            'cookies' => 'Politique des Cookies',
            'legal' => 'Mentions Légales',
            default => 'Mentions Légales',
        };
    }
}
