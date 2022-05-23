import "iframe-resizer/js/iframeResizer";

import '../Components/Atom/StyleguideRefreshIFrame/StyleguideRefreshIFrame';
import '../Components/Atom/StyleguideSelect/StyleguideSelect';
import '../Components/Organism/StyleguideToolbar/StyleguideToolbar';
import '../Components/Atom/StyleguideScrollTop/StyleguideScrollTop';
import '../Components/Atom/ViewportNavigation/ViewportNavigation';
import '../Components/Atom/LanguageNavigation/LanguageNavigation';
import '../Components/Molecule/EditFixtures/EditFixtures';

iFrameResize({ heightCalculationMethod: 'taggedElement', warningTimeout: 0 }, '.iframeResize');
